<?php
class DeleteInvoicesAction extends AppModel
{

    public function __construct()
    {
        parent::__construct();
        // Language::loadLang('delete_invoices', null, PLUGINDIR . 'delete_invoices' . DS . 'language' . DS);
    }

	/**
	 * Delete Invoice
	 */
	public function delete($invoice_id = null, $add_proforma = null)
	{
		if (!$invoice_id)
			return;

        if (!isset($this->Invoices))
            Loader::loadModels($this, array('Invoices'));

		$supported_types = array("void");
		
		if ($add_proforma == "true")
			$supported_types = array("void", "proforma");
		
		// Get this current invoice
		$invoice = $this->Invoices->get($invoice_id);

		if (in_array($invoice->status, $supported_types)) {
			// Delete Invoice
			$this->Record->from("invoice_delivery")->where("invoice_id", "=", $invoice_id)->delete();
			$this->Record->from("invoice_lines")->
				leftJoin("invoice_line_taxes", "invoice_line_taxes.line_id", "=", "invoice_lines.id", false)->
				where("invoice_lines.invoice_id", "=", $invoice_id)->delete(array("invoice_line_taxes.*", "invoice_lines.*"));
			$this->Record->from("invoices")->where("id", "=", $invoice_id)->delete();
			// Delete recuring invoice
			$invoice_recur_id = $this->Invoices->getRecurringFromInvoices($invoice_id);

			if ($invoice_recur_id)
				$this->Invoices->deleteRecurring($invoice_recur_id);			

		}
	}
	
	/**
	 * Delete Invoice
	 */
	public function deleteInvoices($company_id, $cancel_days, $add_proforma)
	{
        // Can not proceed unless values are non-empty
        if ($cancel_days === '')
            return;

		$supported_types = array("void");
		
		if ($add_proforma == "true")
			$supported_types = array("void", "proforma");
		
        $invoice_timelife = $this->dateToUtc($this->Date->cast(strtotime('-' . $cancel_days . ' days'), 'c'));		

		$invoices = $this->Record->select("*")->
			from("invoices")->
			where("invoices.status", "in", $supported_types)->
			where("invoices.date_billed", '<=', $invoice_timelife)->
			fetchAll();

		// if no invoices stop
		if (empty($invoices))
			return; 	

        foreach ($invoices as $invoice)
			$this->delete($invoice->id, $add_proforma);
			
	}

	/**
	 * Delete Invoice
	 */
	public function massAction($company_id, $cancel_days, $add_proforma=null)
	{
        // Can not proceed unless values are non-empty
        if ($cancel_days === '')
            return;
		
		$supported_types = array("void");
		
		if ($add_proforma == "true")
			$supported_types = array("void", "proforma");

        $invoice_timelife = $this->dateToUtc($this->Date->cast(strtotime('-' . $cancel_days . ' days'), 'c'));		

		$invoices = $this->Record->select("*")->
			from("invoices")->
			where("invoices.status", "in", $supported_types)->
			where("invoices.date_billed", '<=', $invoice_timelife)->
			fetchAll();

		// if no invoices stop
		if (empty($invoices))
			return 0; 	

        foreach ($invoices as $invoice)
			$this->delete($invoice->id, $add_proforma);
			
		return count($invoices);			
	}
	
	
}
