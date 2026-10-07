<?php
/**
 * Payment Controller
 * Manages gym payment ledger, receipt printing, and dues collection.
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../modules/payments/PaymentService.php';
require_once __DIR__ . '/../modules/branches/BranchService.php';

class PaymentController {
    private $paymentService;
    private $branchService;

    public function __construct() {
        Auth::requireLogin();
        $this->paymentService = new PaymentService();
        $this->branchService = new BranchService();
    }

    /**
     * Payment transactions ledger
     */
    public function index() {
        Auth::requirePermission('payments.view');

        $user = Auth::user();
        $selectedBranch = $_GET['branch_id'] ?? ($user['role'] === 'superadmin' ? null : $user['branch_id']);
        
        $filters = [
            'branch_id' => $selectedBranch,
            'payment_method' => $_GET['payment_method'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $payments = $this->paymentService->getPayments($filters);
        $metrics = $this->paymentService->getFinancialMetrics($selectedBranch);
        $branches = $this->branchService->getActiveBranches();

        View::render('admin/payments/index', [
            'payments' => $payments,
            'metrics' => $metrics,
            'branches' => $branches,
            'filters' => $filters,
            'selectedBranch' => $selectedBranch
        ]);
    }

    /**
     * Outstanding dues ledger
     */
    public function dues() {
        Auth::requirePermission('payments.view');

        $user = Auth::user();
        $selectedBranch = $_GET['branch_id'] ?? ($user['role'] === 'superadmin' ? null : $user['branch_id']);
        
        $dues = $this->paymentService->getOutstandingDues($selectedBranch);
        $branches = $this->branchService->getActiveBranches();
        $metrics = $this->paymentService->getFinancialMetrics($selectedBranch);

        View::render('admin/payments/dues', [
            'dues' => $dues,
            'branches' => $branches,
            'metrics' => $metrics,
            'selectedBranch' => $selectedBranch
        ]);
    }

    /**
     * Process quick collection / record payment
     */
    public function collect() {
        Auth::requirePermission('payments.collect');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validateToken();

            $validator = new Validator();
            $data = $validator->validate($_POST, [
                'member_id' => 'required',
                'amount' => 'required',
                'payment_method' => 'required'
            ]);

            if ($validator->hasErrors()) {
                Session::flash('error', $validator->getFirstError());
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/admin/payments'));
                exit;
            }

            try {
                $res = $this->paymentService->recordPayment($data, Auth::id());
                Session::flash('success', "Payment of ₹" . number_format($data['amount'], 2) . " recorded successfully! Receipt: " . $res['receipt_number']);
                header('Location: /admin/payments/receipt?id=' . $res['payment_id']);
                exit;
            } catch (Exception $e) {
                Session::flash('error', "Payment failed: " . $e->getMessage());
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/admin/payments'));
                exit;
            }
        }
    }

    /**
     * View and Print standard official receipt
     */
    public function receipt() {
        Auth::requirePermission('payments.view');

        $id = intval($_GET['id'] ?? 0);
        $payment = $this->paymentService->getPaymentReceipt($id);

        if (!$payment) {
            Session::flash('error', 'Receipt not found.');
            header('Location: /admin/payments');
            exit;
        }

        View::render('admin/payments/receipt', [
            'payment' => $payment
        ]);
    }

    /**
     * Thermal printer receipt view (80mm width)
     */
    public function thermal() {
        Auth::requirePermission('payments.view');

        $id = intval($_GET['id'] ?? 0);
        $payment = $this->paymentService->getPaymentReceipt($id);

        if (!$payment) {
            die("Receipt not found");
        }

        // Render standalone thermal view
        require_once __DIR__ . '/../views/admin/payments/thermal.php';
        exit;
    }
}
