<?php
class AdminManagePlugin extends AppController
{
    /**
     * Performs necessary initialization
     */
    private function init()
    {
        // Require login
        $this->parent->requireLogin();

        $this->uses(array('DeleteInvoices.DeleteInvoicesSettings', 'DeleteInvoices.DeleteInvoicesAction'));

        Language::loadLang('admin_manage_plugin', null, PLUGINDIR . 'delete_invoices' . DS . 'language' . DS);

        // Set the page title
        $this->parent->structure->set('page_title', Language::_('AdminManagePlugin.'. Loader::fromCamelCase($this->action ? $this->action : 'index'). '.page_title', true));

        // Set the view to render for all actions under this controller
        $this->view->setView(null, 'DeleteInvoices.default');
    }

    /**
     * Returns the view to be rendered when managing this plugin
     */
    public function index()
    {
        $this->init();

        $vars = (object) $this->DeleteInvoicesSettings->getSettings($this->parent->company_id);
		
        if (!empty($this->post)) {

			if (isset($this->post['mass_action'])) {
				
				$deleted = $this->DeleteInvoicesAction->massAction($this->parent->company_id, $this->post['mass_cancel_days'], $this->post['mass_add_proforma']);
			
				if (($error = $this->DeleteInvoicesSettings->errors())) {
					$this->parent->setMessage('error', $error);
				} else {
					$this->parent->setMessage('message', Language::_('AdminManagePlugin.!success.invoices_deleted', true, $deleted));
				}
				
				$vars = (object)$this->post;
				
			}
			else {

				$this->DeleteInvoicesSettings->setSettings($this->parent->company_id, $this->post);

				if (($error = $this->DeleteInvoicesSettings->errors())) {
					$this->parent->setMessage('error', $error);
				} else {
					$this->parent->setMessage('message', Language::_('AdminManagePlugin.!success.settings_saved', true));
				}
				
				$vars = (object)$this->post;

			}
		
            
        }

        $days = $this->getDays(1, 730);
        $add_proforma = array('false' => "No", 'true' => "Yes");
		$donate_url = "https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2FQKWFL73CV9N";
        // Set the view to render
        return $this->partial('admin_manage_plugin', compact('vars', 'days', 'add_proforma', 'donate_url'));
    }
	
    /**
     * Fetch days
     *
     * @param int $min_days
     * @param int $max_days
     * @return array
     */
    private function getDays($min_days, $max_days)
    {
        $days = array('' => Language::_('AdminManagePlugin.getDays.never', true));
        for ($i = $min_days; $i <= $max_days; $i++) {
            $days[$i] = Language::_('AdminManagePlugin.getDays.text_day'. ($i === 1 ? '' : 's'), true, $i);
        }
        return $days;
    }
	
}
