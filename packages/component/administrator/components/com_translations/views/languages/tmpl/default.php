<? defined('KOOWA') or die('Called'); ?>

<table class="table">
	<thead>
		<tr>
			<th>#</th>
			<th>Language</th>
			<th>Tag</th>
			<th>Translated</th>
		</tr>
	</thead>
	<tbody>
		<? $i = 1; ?>
		<? foreach($languages as $language) : ?>
			<tr>
				<td><?= $i; ?></td>
				<td><?= $language->title; ?></td>
				<td><?= $language->lang_code; ?></td>
				<td><?= $language->getTranslatedPercentage() ?></td>
			</tr>
			<? $i++; ?>
		<? endforeach; ?>
	</tbody>
</table>