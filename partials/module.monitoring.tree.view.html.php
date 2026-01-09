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

$this->includeJsFile('monitoring.host.view.refresh.js.php');

$form = (new CForm())->setName('host_view');

$table = (new CTableInfo())->addClass('services-tree');

$view_url = $data['view_curl']->getUrl();

$table->setHeader([
	make_sorting_header(_('Name'), 'name', $data['sort'], $data['sortorder'], $view_url),
	(new CColHeader(_('Status'))),
	(new CColHeader(_('SLA (%)')))->setAttribute('data-col', 'sla'),
	(new CColHeader(_('SLO (%)')))->setAttribute('data-col', 'slo'),
	(new CColHeader(_('SLA Name')))->setAttribute('data-col', 'sla_name'),
	(new CColHeader(_('Uptime')))->setAttribute('data-col', 'uptime'),
	(new CColHeader(_('Downtime')))->setAttribute('data-col', 'downtime'),
	(new CColHeader(_('Error Budget')))->setAttribute('data-col', 'error_budget'),
	(new CColHeader(_('Root cause')))->setAttribute('data-col', 'root_cause')
]);

foreach ($data['root_services'] as $serviceid) {
	$rows = [];
	addServiceRow($data, $rows, $serviceid, 0, false);
	foreach ($rows as $row) {
		$table->addRow($row);
	}
}

$expand_all = (new CButton('expand_all', _('Expand all')))
	->addClass(ZBX_STYLE_BTN_ALT)
	->addClass('js-expand-all');
$collapse_all = (new CButton('collapse_all', _('Collapse all')))
	->addClass(ZBX_STYLE_BTN_ALT)
	->addClass('js-collapse-all');
$form->addItem((new CDiv([$expand_all, $collapse_all]))->addClass('tree-actions'));
$form->addItem($table);
$form->addItem((new CDiv([$expand_all, $collapse_all]))->addClass('tree-actions'));
echo $form;

function addServiceRow(array $data, array &$rows, string $serviceid, int $level, bool $parent_collapsed): void {
	if (!array_key_exists($serviceid, $data['services'])) {
		return;
	}

	$service = $data['services'][$serviceid];
	$is_collapsed = $service['is_collapsed'];

	$toggle_tag = null;
	if ($service['children']) {
		$toggle_tag = (new CSimpleButton())
			->addClass(ZBX_STYLE_TREEVIEW)
			->addClass('js-toggle')
			->addItem(
				(new CSpan())->addClass($service['is_collapsed'] ? ZBX_STYLE_ARROW_RIGHT : ZBX_STYLE_ARROW_DOWN)
		);
		$toggle_tag->setAttribute(
			'data-service_id_'.$service['serviceid'],
			$service['serviceid']
		);
	}

	$name_col = new CCol();
	for ($i = 0; $i < $level * 5; $i++) {
		$name_col->addItem(NBSP_BG());
	}
	if ($toggle_tag !== null) {
		$name_col->addItem($toggle_tag);
	}
	else {
		$name_col->addItem((new CSpan())->addClass(ZBX_STYLE_TREEVIEW));
	}
	$service_link = (new CUrl('zabbix.php'))
		->setArgument('action', 'service.list')
		->setArgument('serviceid', $service['serviceid']);
	if ($service['path']) {
		$service_link->setArgument('path', $service['path']);
	}
	$name_col->addItem(new CLink($service['name'], $service_link));

	$service_status_value = array_key_exists('status_calc', $service) ? $service['status_calc'] : $service['status'];
	$status_meta = getServiceStatusMeta($service_status_value);
	$status_text = $status_meta['text'];
	$status_class = $status_meta['class'];

	$sli = $service['sla']['sli'];
	$slo = $service['sla']['slo'] ?? null;
	$sla_percent = ($sli !== null) ? ($sli . '%') : '-';
	$slo_percent = ($slo !== null) ? ($slo . '%') : '-';
	$slo_class = '';
	if ($sli !== null && $slo !== null) {
		$slo_class = ((float) $sli >= (float) $slo) ? ZBX_STYLE_GREEN : ZBX_STYLE_RED;
	}
	$sla_name = $service['sla']['sla_name'] ?? '-';
	$slaid = $service['sla']['slaid'] ?? null;
	$sla_name_cell = $sla_name;
	if ($slaid !== null && $sla_name !== '-') {
		$sla_name_cell = new CLink($sla_name, (new CUrl('zabbix.php'))
			->setArgument('action', 'slareport.list')
			->setArgument('filter_slaid', $slaid)
			->setArgument('filter_date_from', '')
			->setArgument('filter_date_to', '')
			->setArgument('filter_set', 1));
	}
	$uptime = $service['sla']['uptime'] ?? '-';
	$downtime = $service['sla']['downtime'] ?? '-';
	$error_budget = $service['sla']['error_budget'] ?? '-';
	$downtime_sec = $service['sla']['downtime_sec'] ?? null;
	$error_budget_sec = $service['sla']['error_budget_sec'] ?? null;
	$downtime_class = '';
	if ($downtime_sec !== null && $error_budget_sec !== null && $error_budget_sec > 0) {
		if ($downtime_sec > ($error_budget_sec / 2)) {
			$downtime_class = ZBX_STYLE_RED;
		}
	}
	$root_cause_items = [];
	if (array_key_exists('root_causes', $service) && $service['root_causes']) {
		$problems = $service['root_causes'];
		$max_show = 3;
		$shown = array_slice($problems, 0, $max_show);
		$remaining = count($problems) - count($shown);

		foreach ($shown as $problem) {
			$problem_name = $problem['name'] ?? '';
			$problem_eventid = $problem['eventid'] ?? null;
			$problem_triggerid = null;
			if (array_key_exists('object', $problem)
					&& defined('EVENT_OBJECT_TRIGGER')
					&& $problem['object'] == EVENT_OBJECT_TRIGGER) {
				$problem_triggerid = $problem['objectid'] ?? null;
			}

			if ($problem_eventid !== null && $problem_triggerid !== null) {
				$root_cause_items[] = (new CSpan([
					'• ',
					new CLink($problem_name, (new CUrl('tr_events.php'))
						->setArgument('triggerid', $problem_triggerid)
						->setArgument('eventid', $problem_eventid))
				]))
				->addClass('root-cause-item')
				->setAttribute('title', $problem_name);
			}
			else {
				$root_cause_items[] = (new CSpan('• '.$problem_name))
					->addClass('root-cause-item');
			}
		}

		if ($remaining > 0) {
			$root_cause_items[] = (new CSpan('['.$remaining.' more problem]'))
				->addClass('root-cause-more');
		}
	}

	$table_row = new CRow([
		$name_col,
		(new CCol((new CSpan($status_text))->addClass($status_class))),
		(new CCol($sla_percent))->setAttribute('data-col', 'sla'),
		($slo_class ? (new CCol($slo_percent))->addClass($slo_class) : new CCol($slo_percent))
			->setAttribute('data-col', 'slo'),
		(new CCol($sla_name_cell))->setAttribute('data-col', 'sla_name'),
		(new CCol($uptime))->setAttribute('data-col', 'uptime'),
		($downtime_class ? (new CCol($downtime))->addClass($downtime_class) : new CCol($downtime))
			->setAttribute('data-col', 'downtime'),
		(new CCol($error_budget))->setAttribute('data-col', 'error_budget'),
		(new CCol($root_cause_items))->setAttribute('data-col', 'root_cause')
	]);

	if ($parent_collapsed) {
		$table_row->addClass(ZBX_STYLE_DISPLAY_NONE);
	}

	addParentServiceClass($data, $table_row, $service['parent_serviceid']);
	$rows[] = $table_row;

	foreach ($service['children'] as $child_serviceid) {
		addServiceRow($data, $rows, $child_serviceid, $level + 1, $parent_collapsed || $is_collapsed);
	}
}

function addParentServiceClass(array $data, &$element, string $parent_serviceid): void {
	if ($parent_serviceid !== '0' && array_key_exists($parent_serviceid, $data['services'])) {
		$element->setAttribute(
			'data-service_id_'.$parent_serviceid,
			$parent_serviceid
		);
	}
}

function NBSP_BG(): CHtmlEntityBG {
	return new CHtmlEntityBG('&nbsp;');
}

class CHtmlEntityBG {
	private $entity = '';
	public function __construct(string $entity) {
		$this->entity = $entity;
	}
	public function toString(): string {
		return $this->entity;
	}
}

function getServiceStatusMeta($status): array {
	$status = (int) $status;
	if ($status == -1) {
		return ['text' => _('OK'), 'class' => ZBX_STYLE_GREEN];
	}
	if ($status >= 0 && $status <= 5) {
		return [
			'text' => CSeverityHelper::getName($status),
			'class' => CSeverityHelper::getStatusStyle($status)
		];
	}

	return ['text' => _('Problem'), 'class' => ZBX_STYLE_RED];
}
