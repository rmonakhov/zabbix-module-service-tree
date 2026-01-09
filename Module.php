<?php declare(strict_types = 1);
 
namespace Modules\ModTreeService;
 
use APP;
use CControllerHost;
use CControllerProblem;
use CControllerLatest;
use Modules\ModTreeService\Actions\CControllerTreeService;
use CControllerTabFilterProfileUpdate;
use CController as CAction;
 
class Module extends \Zabbix\Core\CModule {
	/**
	 * Initialize module.
	 */
	public function init(): void {
		// Initialize main menu (CMenu class instance).
		APP::Component()->get('menu.main')
			->findOrAdd(_('Services'))
				->getSubmenu()
					->insertAfter('Services', (new \CMenuItem(_('Services tree')))
						->setAction('treeservice.view')
					);
	}
 
	/**
	 * Event handler, triggered before executing the action.
	 *
	 * @param CAction $action  Action instance responsible for current request.
	 */
	public function onBeforeAction(CAction $action): void {
		CControllerTabFilterProfileUpdate::$namespaces = [
                CControllerHost::FILTER_IDX => CControllerHost::FILTER_FIELDS_DEFAULT,
				CControllerTreeService::FILTER_IDX => CControllerTreeService::FILTER_FIELDS_DEFAULT,
				CControllerProblem::FILTER_IDX => CControllerProblem::FILTER_FIELDS_DEFAULT,
                CControllerLatest::FILTER_IDX => CControllerLatest::FILTER_FIELDS_DEFAULT
        ];
	}
 
	/**
	 * Event handler, triggered on application exit.
	 *
	 * @param CAction $action  Action instance responsible for current request.
	 */
	public function onTerminate(CAction $action): void {
	}
}
?>
