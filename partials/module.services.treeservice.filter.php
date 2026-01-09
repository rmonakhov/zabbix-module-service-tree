<?php declare(strict_types = 1);
/**
 * @var CView $this
 * @var array $data
 */

$filter = array_key_exists('filter_src', $data) ? $data['filter_src'] : $data;
$filter_name = array_key_exists('name', $filter) ? $filter['name'] : '';
$filter_cols = array_key_exists('cols', $filter) ? $filter['cols'] : [];

$columns = [
	['id' => 'sla', 'label' => _('SLA (%)')],
	['id' => 'slo', 'label' => _('SLO (%)')],
	['id' => 'sla_name', 'label' => _('SLA Name')],
	['id' => 'uptime', 'label' => _('Uptime')],
	['id' => 'downtime', 'label' => _('Downtime')],
	['id' => 'error_budget', 'label' => _('Error Budget')],
	['id' => 'root_cause', 'label' => _('Root cause')]
];

$form = (new CForm())
	->setName('filter')
	->setMethod('get')
	->setId('filter')
	->addClass('filter-form')
	->setAttribute('action', 'zabbix.php')
	->addItem(new CVar('action', 'treeservice.view'))
	->addItem(new CVar('filter_name', $filter['filter_name'] ?? ''));

$form_list = new CFormList('treeserviceFilterList');
$form_list->addRow(
	new CLabel(_('Service name'), 'filter_name'),
	(new CTextBox('name', $filter_name))
		->setWidth(ZBX_TEXTAREA_MEDIUM_WIDTH)
		->setAttribute('id', 'filter_name')
);

$cols_list = new CList();
foreach ($columns as $column) {
	$checkbox = (new CCheckBox('cols[]', $column['id']))
		->setId('col_'.$column['id'])
		->setChecked(in_array($column['id'], $filter_cols));
	$cols_list->addItem(new CListItem([$checkbox, new CLabel($column['label'], 'col_'.$column['id'])]));
}
$cols_list->addClass('filter-columns');

$form_list->addRow(new CLabel(_('Columns')), $cols_list);

$form->addItem($form_list);
echo $form;
