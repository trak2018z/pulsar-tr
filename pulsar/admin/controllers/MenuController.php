<?php
namespace Pulsar\Admin;
/*
 *  This file is part of Pulsar CMS
 *  Copyright (c) by sobiemir <sobiemir@aculo.pl>
 *     ___       __            
 *    / _ \__ __/ /__ ___ _____
 *   / ___/ // / (_-</ _ `/ __/
 *  /_/   \_,_/_/___/\_,_/_/
 *
 *  This source file is subject to the New BSD License that is bundled
 *  with this package in the file LICENSE.txt.
 *
 *  You should have received a copy of the New BSD License along with
 *  this program. If not, see <http://www.licenses.aculo.pl/>.
 */

use Phalcon\Db\Column;
use Phalcon\Mvc\{Controller, Model\Resultset};
use Phalcon\Http\Response;
use Pulsar\Model\{Menu, Language};
use Pulsar\Helper\{Utils, ControlElement};

class MenuController extends Controller
{
	public function indexAction(): void
	{
		$switch = [
			new ControlElement( 1, 'Przetłumaczone' ),
			new ControlElement( 2, 'Do tłumaczenia' )
		];

		$data = [
			// przetłumaczone elementy, limit 30, sortowanie po 'order'
			Menu::find([
				'conditions' => [[
					'id_language = :lang:',
					[ 'lang' => $this->config->cms->language ],
					[ 'lang' => \PDO::PARAM_STR ]
				]],
				'order' => '[order]',
				'limit' => 30
			]),
			// nieprzetłumaczone elementy, limit 30, brak sortowania
			// sortowanie jest wyłączone z racji tego, iż każdy język może mieć
			// różne sortowanie elementów
			Menu::find([
				'group'  => 'id',
				'having' => "id_language != :lang:",
				'bind'   => [
					'lang' => $this->config->cms->language
				],
				'bindTypes' => [
					'lang' => \PDO::PARAM_STR
				],
				'limit' => 30
			])
		];

		$this->view->setVars([
			'data'       => $data,
			'switch'     => $switch,
			'title'      => 'Pulsar :: Menu',
			'breadcrumb' => [
				[
					'name' => 'Treść',
					'url'  => '/admin/content'
				], [
					'name' => 'Menu',
					'url'  => '/admin/menu'
				]
			],
			'hasSidebar' => true,
			'topButtons' => [
				[
					'name'  => 'Nowe menu',
					'title' => 'Nowe menu',
					'url'   => '/admin/menu/new',
					'id'    => 'menu-create-button',
					'class' => 'fa fa-plus'
				]
			],
			'pageAccess' => [
				'admin'     => true,
				'moderator' => true,
				'editor'    => false,
				'user'      => false
			],
			'showColumn' => [
				'order'         => true,
				'id'            => true,
				'name'          => false,
				'online'        => true,
				'action-clone'  => false,
				'action-edit'   => true,
				'action-remove' => true
			]
		]);
	}

	public function newAction()
	{
		if( $this->postRedirect('new') )
		{
			$response = new Response();
			return $response->redirect( "admin/menu" );
		}

		Language::setLanguage( $this->config->cms->language );
		
		// pobierz listę języków
		$all = Language::getFrontend();
		$cur = Language::getCurrent();

		// wygeneruj nowy identyfikator dla menu
		$data = [];
		$id   = Utils::GenerateBinGUID();

		// utwórz puste menu dla każdego z dostępnych języków
		foreach( $all as $lang )
		{
			$menu = new Menu();
			$menu->id = $id;
			$menu->id_language = $lang->id;

			$data[] = $menu;
		}

		// ustaw zmienne dla widoku
		$this->view->setVars([
			'languages'  => $all,
			'language'   => $cur,
			'data'       => $data,
			'title'      => 'Pulsar :: Nowe menu',
			'breadcrumb' => [
				[
					'name' => 'Treść',
					'url'  => '/admin/content'
				], [
					'name' => 'Menu',
					'url'  => '/admin/menu'
				], [
					'name' => 'Nowe',
					'url'  => '/admin/menu/new'
				]
			],
			'hasSidebar' => false,
			'topButtons' => [
				[
					'name'  => 'Powrót',
					'url'   => '/admin/menu',
					'id'    => 'menu-back-button',
					'class' => 'fa fa-chevron-left'
				]
			]
		]);
	}

	public function editAction( string $id = null ): void
	{
		if( $this->postRedirect('edit', [$id]) )
			return;

		$bin = Utils::GUIDToBin( $id );

		Language::setLanguage( $this->config->cms->language );
		
		$all = Language::getFrontend();
		$cur = Language::getCurrent();

		// wyszukaj menu do edycji
		// zwracana jest lista, ponieważ edytowane są wszystkie języki
		$menus = Menu::find([
			'conditions' => [[
				'id = :id:',
				[ 'id' => $bin ],
				[ 'id' => \PDO::PARAM_STR ]
			]]
		]);

		$data    = [];
		$avlangs = [];

		// zapisz identyfikatory znanych języków
		foreach( $all as $lang )
			$avlangs[$lang->id] = true;

		// odrzuć nieużywane języki
		foreach( $menus as $menu )
			if( isset($avlangs[$menu->id_language]) )
			{
				$data[] = $menu;
				$avlangs[$menu->id_language] = false;
			}

		// uzupełnij pustymi danymi brakujące języki
		foreach( $avlangs as $langid => $missing )
		{
			if( !$missing )
				continue;

			$menu = new Menu();
			$menu->id = $id;
			$menu->id_language = $langid;

			$data[] = $menu;
		}

		// menu o podanym indeksie nie istnieje!
		if( count($data) == 0 )
			throw new \Exception( 'Podany rekord nie istnieje!' );

		$this->view->setVars([
			'languages' => $all,
			'language'  => $cur,

			'title'      => 'Pulsar :: Edycja menu',
			'breadcrumb' => [
				[
					'name' => 'Treść',
					'url'  => '/admin/content'
				], [
					'name' => 'Menu',
					'url'  => '/admin/menu'
				], [
					'name' => 'Edycja',
					'url'  => '/admin/menu/edit/' . $id
				]
			],
			'data'       => $data,
			'hasSidebar' => false,
			'topButtons' => [
				[
					'name'  => 'Powrót',
					'url'   => '/admin/menu',
					'id'    => 'menu-back-button',
					'class' => 'fa fa-chevron-left'
				]
			]
		]);
	}

	private function postRedirect( string $action, ?array $args = null ): bool
	{
		if( !$this->request->isPost() )
			return false;

		if( $action === 'new' )
			$this->addMenu();

		return true;
	}

	private function addMenu(): void
	{
		$menu = new Menu();
	}
}
