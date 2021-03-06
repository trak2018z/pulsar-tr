<div class="items-vertical lightgrey-back fill-free">

	<!-- formularz edycji i tworzenia nowego elementu -->
	{{ tag.beginForm([
		'id'    : 'P01_FormEdit',
		'source': data,
		'action': saveAction
	]) }}

		<div class="head-bar mb00">
			<h2 class="mb5 mt5">
				{{ isEditing ? 'Edycja menu' : 'Nowe menu' }}
			</h2>
		</div>

		<!-- kontrolka przełączająca pomiędzy językami -->
		{{ tag.tabControl([
			'source'  : languages,
			'selected': language,
			'class'   : 'white-back head-bar',
			'data'    : [
				'search' : '#P01_Container',
				'message': '#P01_NotExistMsg',
				'remove' : '#P01_RemoveLang',
				'create' : '#P01_AddLang',
				'form'   : '#P01_FormEdit'
			]
		]) }}

		<!-- flagi stanu danego języka -->
		{{ tag.hiddenModelFlags([
			'id': 'P01_Flag'
		]) }}

		<p id="P01_NotExistMsg" class="message info hidden m15">
			<b>Tłumaczenie dla wybranego języka nie istnieje.</b><br />

			Aby go utworzyć, kliknij w przycisk dodawania języka, który znajduje
			się po prawej stronie pod wyświetlaną wiadomością.
		</p>

		<table id="P01_Container" class="w100p form container hidden">
			<!-- nazwa menu -->
			<tr>
				<td><label for="menu-name">Nazwa:</label></td>
				<td>
					{{ tag.textBoxLang([
						'name' : 'name',
						'id'   : 'P01_Name',
						'class': 'w100p'
					]) }}
				</td>
				<td class="description">
					Wyświetlana jest głównie na panelu bocznym w PA.
				</td>
			</tr>

			<!-- czy menu jest prywatne? -->
			<tr>
				<td>Szczegóły:</td>
				<td>
					{{ tag.checkBoxLang([
						'name' : 'private',
						'id'   : 'P01_Private',
						'label': 'Prywatne'
					]) }}
				</td>
				<td class="description">
					Menu nie jest dostępne do wyboru dla szablonu.
				</td>
			</tr>

			<!-- czy menu jest dostępne? -->
			<tr>
				<td></td>
				<td>
					{{ tag.checkBoxLang([
						'name' : 'online',
						'id'   : 'P01_Online',
						'label': 'Dostępne'
					]) }}
				</td>
				<td class="description">
					Menu nie jest widoczne w PA poza listą.
				</td>
			</tr>
		</table>

		<!-- kontener z przyciskami -->
		<div class="button-container">
			<button class="blue">
				{{ isEditing ? 'Zapisz zmiany' : 'Dodaj menu' }}
			</button>
			<button id="P01_AddLang" type="button" class="ml-a black hidden">
				Dodaj język
			</button>
			<button id="P01_RemoveLang" class="ml-a black hidden" type="button"
				data-confirm="Czy na pewno chcesz usunąć to tłumaczenie?">
				Usuń język
			</button>
			{% if isEditing %}
				<a href="{{ removeAction }}" class="red button show-confirm"
					data-confirm="Czy na pewno chcesz usunąć to menu?">
					Usuń menu
				</a>
			{% endif %}
		</div>

	{{ tag.endForm() }}
</div>
