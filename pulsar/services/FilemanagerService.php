<?php
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

namespace Pulsar\Service;

class FilemanagerService
{
	public function getRealPath( string $path ): string
	{
		$path = realpath( BASE_PATH . 'files/' . $path );

		// sprawdź czy ścieżka zawiera nazwę bazową - nie pozwól cofnąć się
		// poza folder w którym użytkownik może wykonywać akcje
		if( strpos( $path, BASE_PATH . 'files' ) !== 0 )
			throw new \Exception("Not found");

		return $path;
	}

	public function listDirectories( string $path, bool $sub ): array
	{
		$directories = [];
		$iterator    = new \DirectoryIterator( $path );

		// szukaj katalogów w katalogu
		foreach( $iterator as $entity )
		{
			if( !$entity->isDir() || $entity->isDot() )
				continue;

			// twórz listę katalogów gdy funkcja na to zezwala
			$directories[] = [
				'name'     => $entity->getFilename(),
				'modify'   => $entity->getMTime(),
				'access'   => $entity->getATime(),
				'children' => $sub
					? $this->listDirectories( $entity->getRealPath(), true )
					: []
			];
		}
		return $directories;
	}

	public function listEntities( string $path ): array
	{
		$entities = [];
		$iterator = new \DirectoryIterator( $path );

		// szukaj elementów w katalogu
		foreach( $iterator as $entity )
		{
			// nie uwzględniaj linków
			if( $entity->isLink() || $entity->isDot() )
				continue;

			// uzupełnij informacje dla każdego elementu
			$entities[] = [
				'name'   => $entity->getFilename(),
				'size'   => $entity->getSize(),
				'modify' => $entity->getMTime(),
				'access' => $entity->getATime(),
				'type'   => $entity->getType(),
				'mime'   => $entity->isDir()
					? ''
					: mime_content_type( $entity->getPathname() )
			];
		}
		return $entities;
	}
}
