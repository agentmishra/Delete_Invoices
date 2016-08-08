<?php
class DeleteInvoicesSettings extends AppModel
{
    public function __construct()
    {
        parent::__construct();

        if (!isset($this->SettingsCollection))
            Loader::loadComponents($this, array('SettingsCollection'));

        Language::loadLang('delete_invoices_plugin', null, PLUGINDIR . 'delete_invoices' . DS . 'language' . DS);
    }

    /**
     * Fetches settings
     *
     * @param int $company_id
     * @return array
     */
    public function getSettings($company_id)
    {
        $supported = $this->supportedSettings();
        $company_settings = $this->SettingsCollection->fetchSettings(null, $company_id);
        $settings = array();
        foreach ($company_settings as $setting => $value) {
            if (($index = array_search($setting, $supported)) !== false) {
                $settings[$index] = $value;
            }
        }
        return $settings;
    }

    /**
     * Set settings
     *
     * @param int $company_id
     * @param array $settings Key/value pairs
     */
    public function setSettings($company_id, array $settings)
    {
        if (!isset($this->Companies))
            Loader::loadModels($this, array('Companies'));

        $valid_settings = array();
        foreach ($this->supportedSettings() as $key => $name) {
            if (array_key_exists($key, $settings)) {
                $valid_settings[$name] = $settings[$key];
            }
        }

        $this->Input->setRules($this->getRules($valid_settings));
        if ($this->Input->validates($valid_settings)) {
            $this->Companies->setSettings($company_id, $valid_settings);
        }
    }

    /**
     * Fetch supported settings
     *
     * @return array
     */
    public function supportedSettings()
    {
        return array(
            'cancel_days' => 'delete_invoices.cancel_days',
            'add_proforma' => 'delete_invoices.add_proforma',
        );
    }

    /**
     * Input validate rules
     *
     * @param array $vars
     * @return array
     */
    private function getRules($vars)
    {
        return array(
            'delete_invoices.cancel_days' => array(
                'valid' => array(
                    'rule' => array(array($this, 'isValidDay')),
                    'message' => $this->_('DeleteInvoicesSettings.!error.cancel_days.valid')
                )
            ),
            'delete_invoices.add_proforma' => array(
				'format' => array(
					'if_set' => true,
					'rule' => array("in_array", array("true", "false")),
					'message' => $this->_("DeleteInvoicesSettings.!error.add_proforma.format")
				),
            ),
			
        );
    }

    /**
     * Validate the day given
     *
     * @param string $day
     * @return boolean
     */
    public function isValidDay($day)
    {
        return $day === '' || ($day >= 0 && $day <= 730);
    }
}
