<?php
/**
 * SavingsController - Controller untuk Modul Tabungan, Financial Goals & Alokasi Penempatan Dana
 */
class SavingsController extends Controller
{
    private SavingsModel $savingsModel;

    public function __construct()
    {
        parent::__construct();
        $this->savingsModel = new SavingsModel();
    }

    /**
     * Halaman Utama Savings
     */
    public function index()
    {
        $this->requireService('savings');

        $summary = $this->savingsModel->getOverallSummary();
        $goals = $this->savingsModel->getGoals();

        $this->view('savings.index', [
            'title' => 'Savings & Goals Tabungan',
            'activeNav' => 'home',
            'summary' => $summary,
            'goals' => $goals,
        ]);
    }

    // ==========================================
    // API ENDPOINTS
    // ==========================================

    /**
     * API: Get Overall Summary
     */
    public function apiGetSummary()
    {
        $this->requireService('savings');
        $summary = $this->savingsModel->getOverallSummary();
        $this->json(['success' => true, 'data' => $summary]);
    }

    /**
     * API: Get All Goals
     */
    public function apiGetGoals()
    {
        $this->requireService('savings');
        $status = $this->query('status');
        $goals = $this->savingsModel->getGoals($status);
        $this->json(['success' => true, 'data' => $goals]);
    }

    /**
     * API: Get Goal Detail by ID
     * @param int|string $id
     */
    public function apiGetGoalDetail(int|string $id)
    {
        $this->requireService('savings');
        $goal = $this->savingsModel->getGoalById((int)$id);
        if (!$goal) {
            $this->json(['error' => 'Goal tidak ditemukan'], 404);
        }
        $this->json(['success' => true, 'data' => $goal]);
    }

    /**
     * API: Create New Goal
     */
    public function apiCreateGoal()
    {
        $this->requireService('savings');
        $this->validateCSRF();

        $name = trim((string)$this->input('name', ''));
        $targetAmount = (float)$this->input('target_amount', 0);
        $targetDate = $this->input('target_date', null);
        $category = trim((string)$this->input('category', 'Lainnya'));
        $icon = trim((string)$this->input('icon', 'bi-piggy-bank-fill'));
        $color = trim((string)$this->input('color', '#6366f1'));
        $description = trim((string)$this->input('description', ''));

        if (empty($name)) {
            $this->json(['error' => 'Nama goal/tujuan tabungan wajib diisi'], 422);
        }
        if ($targetAmount <= 0) {
            $this->json(['error' => 'Target nominal harus lebih dari 0'], 422);
        }

        try {
            $goalId = $this->savingsModel->createGoal([
                'name' => $name,
                'target_amount' => $targetAmount,
                'target_date' => !empty($targetDate) ? $targetDate : null,
                'category' => $category,
                'icon' => $icon,
                'color' => $color,
                'description' => $description,
                'status' => 'in_progress',
            ]);

            // If initial allocations provided (e.g. array of {name, account_type, institution, amount})
            $initialAllocations = $this->input('allocations', []);
            if (is_array($initialAllocations)) {
                foreach ($initialAllocations as $alloc) {
                    $allocName = trim((string)($alloc['name'] ?? ''));
                    $allocAmount = (float)($alloc['amount'] ?? 0);
                    if (!empty($allocName)) {
                        $this->savingsModel->createAllocation([
                            'goal_id' => $goalId,
                            'name' => $allocName,
                            'account_type' => $alloc['account_type'] ?? 'Bank / Rekening',
                            'institution' => $alloc['institution'] ?? null,
                            'amount' => $allocAmount,
                            'notes' => $alloc['notes'] ?? null,
                        ]);
                    }
                }
            }

            $createdGoal = $this->savingsModel->getGoalById($goalId);
            $this->json([
                'success' => true,
                'message' => 'Goal tabungan berhasil ditambahkan',
                'data' => $createdGoal
            ]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Gagal membuat goal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Update Goal
     * @param int|string $id
     */
    public function apiUpdateGoal(int|string $id)
    {
        $this->requireService('savings');
        $this->validateCSRF();

        $goal = $this->savingsModel->getGoalById((int)$id);
        if (!$goal) {
            $this->json(['error' => 'Goal tidak ditemukan'], 404);
        }

        $name = trim((string)$this->input('name', $goal['name']));
        $targetAmount = (float)$this->input('target_amount', $goal['target_amount']);
        $targetDate = $this->input('target_date', $goal['target_date']);
        $category = trim((string)$this->input('category', $goal['category']));
        $icon = trim((string)$this->input('icon', $goal['icon']));
        $color = trim((string)$this->input('color', $goal['color']));
        $description = trim((string)$this->input('description', $goal['description'] ?? ''));
        $status = trim((string)$this->input('status', $goal['status']));

        if (empty($name)) {
            $this->json(['error' => 'Nama goal tidak boleh kosong'], 422);
        }
        if ($targetAmount <= 0) {
            $this->json(['error' => 'Target nominal harus lebih besar dari 0'], 422);
        }

        try {
            $this->savingsModel->updateGoal((int)$id, [
                'name' => $name,
                'target_amount' => $targetAmount,
                'target_date' => !empty($targetDate) ? $targetDate : null,
                'category' => $category,
                'icon' => $icon,
                'color' => $color,
                'description' => $description,
                'status' => $status,
            ]);

            $updatedGoal = $this->savingsModel->getGoalById((int)$id);
            $this->json([
                'success' => true,
                'message' => 'Goal berhasil diperbarui',
                'data' => $updatedGoal
            ]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Gagal memperbarui goal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Delete Goal
     * @param int|string $id
     */
    public function apiDeleteGoal(int|string $id)
    {
        $this->requireService('savings');
        $this->validateCSRF();

        $goal = $this->savingsModel->getGoalById((int)$id);
        if (!$goal) {
            $this->json(['error' => 'Goal tidak ditemukan'], 404);
        }

        try {
            $this->savingsModel->deleteGoal((int)$id);
            $this->json(['success' => true, 'message' => 'Goal tabungan berhasil dihapus']);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Gagal menghapus goal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Save / Update Allocation Item (Pos Penempatan)
     */
    public function apiSaveAllocation()
    {
        $this->requireService('savings');
        $this->validateCSRF();

        $allocId = $this->input('id', null);
        $goalId = (int)$this->input('goal_id', 0);
        $name = trim((string)$this->input('name', ''));
        $accountType = trim((string)$this->input('account_type', 'Bank / Rekening'));
        $institution = trim((string)$this->input('institution', ''));
        $amount = (float)$this->input('amount', 0);
        $notes = trim((string)$this->input('notes', ''));

        if ($goalId <= 0) {
            $this->json(['error' => 'ID Goal tidak valid'], 422);
        }
        if (empty($name)) {
            $this->json(['error' => 'Nama pos penempatan (misal: Bibit, Toko, SeaBank) wajib diisi'], 422);
        }
        if ($amount < 0) {
            $this->json(['error' => 'Nominal tidak boleh negatif'], 422);
        }

        try {
            if (!empty($allocId)) {
                // Update existing allocation
                $this->savingsModel->updateAllocation((int)$allocId, [
                    'name' => $name,
                    'account_type' => $accountType,
                    'institution' => $institution,
                    'amount' => $amount,
                    'notes' => $notes,
                ]);
            } else {
                // Create new allocation
                $this->savingsModel->createAllocation([
                    'goal_id' => $goalId,
                    'name' => $name,
                    'account_type' => $accountType,
                    'institution' => $institution,
                    'amount' => $amount,
                    'notes' => $notes,
                ]);
            }

            $updatedGoal = $this->savingsModel->getGoalById($goalId);
            $this->json([
                'success' => true,
                'message' => 'Pos alokasi tabungan berhasil disimpan',
                'data' => $updatedGoal
            ]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Gagal menyimpan pos alokasi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Delete Allocation Item
     * @param int|string $id
     */
    public function apiDeleteAllocation(int|string $id)
    {
        $this->requireService('savings');
        $this->validateCSRF();

        $alloc = $this->savingsModel->getAllocationById((int)$id);
        if (!$alloc) {
            $this->json(['error' => 'Pos alokasi tidak ditemukan'], 404);
        }

        try {
            $goalId = (int)$alloc['goal_id'];
            $this->savingsModel->deleteAllocation((int)$id);
            $updatedGoal = $this->savingsModel->getGoalById($goalId);
            $this->json([
                'success' => true,
                'message' => 'Pos alokasi berhasil dihapus',
                'data' => $updatedGoal
            ]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Gagal menghapus pos alokasi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Record Mutation (Setoran / Penarikan Dana)
     */
    public function apiRecordMutation()
    {
        $this->requireService('savings');
        $this->validateCSRF();

        $goalId = (int)$this->input('goal_id', 0);
        $allocationId = (int)$this->input('allocation_id', 0);
        $type = trim((string)$this->input('type', 'deposit')); // 'deposit' or 'withdraw'
        $amount = (float)$this->input('amount', 0);
        $notes = trim((string)$this->input('notes', ''));
        $logDate = $this->input('log_date', date('Y-m-d'));

        if ($goalId <= 0 || $allocationId <= 0) {
            $this->json(['error' => 'Goal dan Pos Alokasi harus dipilih'], 422);
        }
        if ($amount <= 0) {
            $this->json(['error' => 'Nominal mutasi harus lebih besar dari 0'], 422);
        }
        if (!in_array($type, ['deposit', 'withdraw'])) {
            $this->json(['error' => 'Tipe mutasi tidak valid (harus Setor atau Tarik)'], 422);
        }

        try {
            $res = $this->savingsModel->recordMutation($goalId, $allocationId, $type, $amount, $notes, $logDate);
            $updatedGoal = $this->savingsModel->getGoalById($goalId);
            $this->json([
                'success' => true,
                'message' => ($type === 'deposit' ? 'Setoran' : 'Penarikan') . ' dana berhasil dicatat',
                'data' => $updatedGoal
            ]);
        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * API: Get Daily Snapshot History & Analytics for a Goal
     * @param int|string $id
     */
    public function apiGetGoalHistory(int|string $id)
    {
        $this->requireService('savings');
        $goalId = (int)$id;

        $goal = $this->savingsModel->getGoalById($goalId);
        if (!$goal) {
            $this->json(['error' => 'Goal tidak ditemukan'], 404);
        }

        // Auto trigger daily snapshot check
        $this->savingsModel->autoTriggerDailySnapshot();

        $snapshots = $this->savingsModel->getDailySnapshots($goalId, 60);
        $analytics = $this->savingsModel->getGoalSnapshotAnalytics($goalId);

        $this->json([
            'success' => true,
            'data' => [
                'goal' => $goal,
                'snapshots' => $snapshots,
                'analytics' => $analytics,
            ]
        ]);
    }

    /**
     * API: Capture Snapshot for a specific Goal
     * @param int|string $id
     */
    public function apiCaptureGoalSnapshot(int|string $id)
    {
        $this->requireService('savings');
        $this->validateCSRF();
        $goalId = (int)$id;

        try {
            $snapshot = $this->savingsModel->captureDailySnapshot($goalId);
            $this->json([
                'success' => true,
                'message' => 'Snapshot progress harian berhasil dicapture',
                'data' => $snapshot
            ]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Gagal mencapture snapshot: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Get All Snapshots across all goals
     */
    public function apiGetAllSnapshots()
    {
        $this->requireService('savings');
        $this->savingsModel->autoTriggerDailySnapshot();
        $snapshots = $this->savingsModel->getAllDailySnapshots(100);
        $this->json(['success' => true, 'data' => $snapshots]);
    }

    /**
     * API: Capture Snapshots for all Goals
     */
    public function apiCaptureAllSnapshots()
    {
        $this->requireService('savings');
        $this->validateCSRF();

        try {
            $results = $this->savingsModel->captureAllGoalsSnapshot();
            $this->json([
                'success' => true,
                'message' => 'Snapshot seluruh target berhasil disimpan',
                'data' => $results
            ]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Gagal mencapture snapshot seluruh target: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // MUTUAL FUNDS (REKSADANA) API
    // ==========================================

    /**
     * API: Get Mutual Funds Portfolio List & Summary
     */
    public function apiGetMutualFunds()
    {
        $this->requireService('savings');
        $type = $this->query('type');
        $funds = $this->savingsModel->getMutualFunds($type);
        $summary = $this->savingsModel->getMutualFundsSummary();

        $this->json([
            'success' => true,
            'data' => $funds,
            'summary' => $summary
        ]);
    }

    /**
     * API: Get Master Catalog & Fund Houses for Dropdown/Search
     */
    public function apiGetMasterMutualFunds()
    {
        $this->requireService('savings');
        require_once __DIR__ . '/../services/MutualFundService.php';

        $keyword = (string)$this->query('q', '');
        $type = (string)$this->query('type', '');
        $fundHouse = (string)$this->query('fund_house', '');

        $products = MutualFundService::searchProducts($keyword, $type, $fundHouse);
        $fundHouses = MutualFundService::getFundHouses();

        $this->json([
            'success' => true,
            'data' => [
                'products' => $products,
                'fund_houses' => $fundHouses,
                'total_results' => count($products)
            ]
        ]);
    }

    /**
     * API: Get Mutual Fund Detail by ID
     * @param int|string $id
     */
    public function apiGetMutualFundDetail(int|string $id)
    {
        $this->requireService('savings');
        $fund = $this->savingsModel->getMutualFundById((int)$id);
        if (!$fund) {
            $this->json(['error' => 'Data reksadana tidak ditemukan'], 404);
        }
        $this->json(['success' => true, 'data' => $fund]);
    }

    /**
     * API: Create New Mutual Fund Entry
     */
    public function apiCreateMutualFund()
    {
        $this->requireService('savings');
        $this->validateCSRF();
        require_once __DIR__ . '/../services/MutualFundService.php';

        $fundName = trim((string)$this->input('fund_name', ''));
        $fundHouse = trim((string)$this->input('fund_house', ''));
        $fundType = trim((string)$this->input('fund_type', 'Pasar Uang'));
        $buyDate = trim((string)$this->input('buy_date', date('Y-m-d')));
        $investedAmount = (float)$this->input('invested_amount', 0);
        $buyNav = (float)$this->input('buy_nav', 0);
        $unitsOwned = (float)$this->input('units_owned', 0);
        $isSyariah = (int)$this->input('is_syariah', 0);
        $platform = trim((string)$this->input('platform', 'Bibit'));
        $notes = trim((string)$this->input('notes', ''));

        if (empty($fundName)) {
            $this->json(['error' => 'Nama produk reksadana wajib diisi'], 422);
        }
        if (empty($fundHouse)) {
            $this->json(['error' => 'Manajer Investasi wajib dipilih atau diisi'], 422);
        }
        if ($investedAmount <= 0) {
            $this->json(['error' => 'Modal pembelian investasi harus lebih dari 0'], 422);
        }

        // Auto calculate units or buy NAV if one is missing
        if ($unitsOwned <= 0 && $buyNav > 0) {
            $unitsOwned = round($investedAmount / $buyNav, 4);
        } elseif ($buyNav <= 0 && $unitsOwned > 0) {
            $buyNav = round($investedAmount / $unitsOwned, 4);
        } elseif ($unitsOwned <= 0 && $buyNav <= 0) {
            // Default NAV 1000 if not specified
            $buyNav = 1000.0;
            $unitsOwned = round($investedAmount / 1000.0, 4);
        }

        // Fetch live / current NAV from service
        $liveData = MutualFundService::fetchLiveNav($fundName, $fundHouse, $buyNav);
        $currentNav = (float)($liveData['nav'] ?? $buyNav);
        $lastNav = (float)($liveData['last_nav'] ?? $currentNav);
        $dailyChange = (float)($liveData['change_pct'] ?? 0);

        try {
            $id = $this->savingsModel->createMutualFund([
                'fund_name' => $fundName,
                'fund_house' => $fundHouse,
                'fund_type' => $fundType,
                'buy_date' => $buyDate,
                'invested_amount' => $investedAmount,
                'buy_nav' => $buyNav,
                'units_owned' => $unitsOwned,
                'current_nav' => $currentNav,
                'last_nav' => $lastNav,
                'daily_change_pct' => $dailyChange,
                'is_syariah' => $isSyariah,
                'platform' => $platform,
                'notes' => $notes
            ]);

            $newFund = $this->savingsModel->getMutualFundById($id);
            $this->json([
                'success' => true,
                'message' => 'Reksadana berhasil ditambahkan ke portofolio',
                'data' => $newFund
            ]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Gagal menyimpan data reksadana: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Update Mutual Fund
     * @param int|string $id
     */
    public function apiUpdateMutualFund(int|string $id)
    {
        $this->requireService('savings');
        $this->validateCSRF();
        require_once __DIR__ . '/../services/MutualFundService.php';

        $fundId = (int)$id;
        $existing = $this->savingsModel->getMutualFundById($fundId);
        if (!$existing) {
            $this->json(['error' => 'Data reksadana tidak ditemukan'], 404);
        }

        $fundName = trim((string)$this->input('fund_name', $existing['fund_name']));
        $fundHouse = trim((string)$this->input('fund_house', $existing['fund_house']));
        $fundType = trim((string)$this->input('fund_type', $existing['fund_type']));
        $buyDate = trim((string)$this->input('buy_date', $existing['buy_date']));
        $investedAmount = (float)$this->input('invested_amount', $existing['invested_amount']);
        $buyNav = (float)$this->input('buy_nav', $existing['buy_nav']);
        $unitsOwned = (float)$this->input('units_owned', $existing['units_owned']);
        $currentNav = (float)$this->input('current_nav', $existing['current_nav']);
        $isSyariah = (int)$this->input('is_syariah', $existing['is_syariah']);
        $platform = trim((string)$this->input('platform', $existing['platform'] ?? 'Bibit'));
        $notes = trim((string)$this->input('notes', $existing['notes'] ?? ''));

        if (empty($fundName)) {
            $this->json(['error' => 'Nama produk reksadana wajib diisi'], 422);
        }
        if ($investedAmount <= 0) {
            $this->json(['error' => 'Modal pembelian harus lebih dari 0'], 422);
        }

        if ($unitsOwned <= 0 && $buyNav > 0) {
            $unitsOwned = round($investedAmount / $buyNav, 4);
        } elseif ($buyNav <= 0 && $unitsOwned > 0) {
            $buyNav = round($investedAmount / $unitsOwned, 4);
        }

        try {
            $this->savingsModel->updateMutualFund($fundId, [
                'fund_name' => $fundName,
                'fund_house' => $fundHouse,
                'fund_type' => $fundType,
                'buy_date' => $buyDate,
                'invested_amount' => $investedAmount,
                'buy_nav' => $buyNav,
                'units_owned' => $unitsOwned,
                'current_nav' => $currentNav > 0 ? $currentNav : (float)$existing['current_nav'],
                'is_syariah' => $isSyariah,
                'platform' => $platform,
                'notes' => $notes
            ]);

            $updated = $this->savingsModel->getMutualFundById($fundId);
            $this->json([
                'success' => true,
                'message' => 'Data reksadana berhasil diperbarui',
                'data' => $updated
            ]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Gagal memperbarui data reksadana: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Delete Mutual Fund
     * @param int|string $id
     */
    public function apiDeleteMutualFund(int|string $id)
    {
        $this->requireService('savings');
        $this->validateCSRF();

        $fundId = (int)$id;
        $existing = $this->savingsModel->getMutualFundById($fundId);
        if (!$existing) {
            $this->json(['error' => 'Data reksadana tidak ditemukan'], 404);
        }

        try {
            $this->savingsModel->deleteMutualFund($fundId);
            $this->json([
                'success' => true,
                'message' => 'Reksadana ' . $existing['fund_name'] . ' berhasil dihapus dari portofolio'
            ]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Gagal menghapus reksadana: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Trigger Live NAV Refresh for All Mutual Funds
     */
    public function apiRefreshMutualFundsNav()
    {
        $this->requireService('savings');
        $this->validateCSRF();

        try {
            $res = $this->savingsModel->refreshAllMutualFundsNav();
            $funds = $this->savingsModel->getMutualFunds();
            $summary = $this->savingsModel->getMutualFundsSummary();

            $this->json([
                'success' => true,
                'message' => 'Harga NAB seluruh reksadana berhasil diperbarui secara real-time',
                'meta' => $res,
                'data' => $funds,
                'summary' => $summary
            ]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Gagal memperbarui NAB reksadana: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Get Real-time PPOB Balance with graceful error handling
     */
    public function apiGetPpobBalance()
    {
        $this->requireService('savings');
        try {
            require_once __DIR__ . '/../services/DigiflazzService.php';
            $digiService = new DigiflazzService();
            $res = $digiService->getBalance();
            $this->json($res);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
