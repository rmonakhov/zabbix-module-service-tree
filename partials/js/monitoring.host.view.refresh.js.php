<?php
/*
** Zabbix
** Copyright (C) 2001-2021 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


/**
 * @var CView $this
 */

?>

<script type="text/javascript">
	var expandable_services = [<?php
		$expandable = [];
		foreach ($data['services'] as $serviceid => $service) {
			if ($service['children']) {
				$expandable[] = "'".$serviceid."'";
			}
		}
		echo implode(',', $expandable);
	?>];

	function isChevronCollapsed($chevron) {
		return $chevron.hasClass('<?= ZBX_STYLE_ARROW_RIGHT ?>');
	}

	$('.js-toggle').on('click', function() {
		var $toggle = $(this),
			collapsed = !isChevronCollapsed($toggle.find('span'));
		var service_id = 0;
		for (const key in $toggle[0].attributes) {
			var attr = $toggle[0].attributes[key];
			if (attr.name.startsWith('data-')) {
				service_id = attr.value
				break;
			}
		}
		view.serviceToFromRefreshUrl(service_id, collapsed);

		view.refresh();
	});

	$('.js-expand-all').on('click', function(e) {
		e.preventDefault();
		view.setExpandedServices(expandable_services);
		view.refresh();
	});

	$('.js-collapse-all').on('click', function(e) {
		e.preventDefault();
		view.setExpandedServices([]);
		view.refresh();
	});
</script>
