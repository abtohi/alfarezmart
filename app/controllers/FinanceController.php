<?php
/**
 * FinanceController - Controller untuk Laporan/Pencatatan Keuangan Harian
 */
class FinanceController extends Controller
{
    public function index()
    {
        $this->requireSuperadmin();
        $this->view('finance.index', [
            'title' => 'Keuangan Harian',
            'activeNav' => 'home' // Keeps home menu active in bottom-nav
        ]);
    }
}
