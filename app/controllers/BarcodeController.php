<?php
/**
 * BarcodeController - Scanner dan generate barcode
 */
class BarcodeController extends Controller
{
    public function scanner()
    {
        $this->view('scanner.index', [
            'title' => 'Scan Barcode',
            'activeNav' => 'scan',
        ]);
    }
}
