<?php
/**
 * Progress & Transformation Controller
 * Manages photo checkpoints, body measurements, before/after slider, and privacy controls.
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../modules/progress/ProgressService.php';
require_once __DIR__ . '/../modules/members/MemberService.php';

class ProgressController {
    private $progressService;
    private $memberService;

    public function __construct() {
        Auth::requireLogin();
        $this->progressService = new ProgressService();
        $this->memberService = new MemberService();
    }

    /**
     * Member progress timeline & gallery
     */
    public function index() {
        Auth::requirePermission('members.view');

        $memberId = intval($_GET['member_id'] ?? 0);
        $member = $this->memberService->getMemberById($memberId);

        if (!$member) {
            Session::flash('error', 'Member not found.');
            header('Location: /admin/members');
            exit;
        }

        $checkpoints = $this->progressService->getMemberProgress($memberId, Auth::user());
        $comparison = $this->progressService->getComparisonData($memberId, Auth::user());

        View::render('admin/progress/index', [
            'member' => $member,
            'checkpoints' => $checkpoints,
            'comparison' => $comparison
        ]);
    }

    /**
     * New checkpoint form
     */
    public function create() {
        Auth::requirePermission('members.edit');

        $memberId = intval($_GET['member_id'] ?? 0);
        $member = $this->memberService->getMemberById($memberId);

        if (!$member) {
            Session::flash('error', 'Member not found.');
            header('Location: /admin/members');
            exit;
        }

        View::render('admin/progress/form', [
            'member' => $member
        ]);
    }

    /**
     * Store checkpoint action
     */
    public function store() {
        Auth::requirePermission('members.edit');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validateToken();

            $memberId = intval($_POST['member_id'] ?? 0);
            $member = $this->memberService->getMemberById($memberId);

            if (!$member) {
                Session::flash('error', 'Member not found.');
                header('Location: /admin/members');
                exit;
            }

            try {
                $this->progressService->recordCheckpoint($_POST, $_FILES, Auth::id());
                Session::flash('success', 'Transformation checkpoint logged successfully!');
                header("Location: /admin/progress?member_id={$memberId}");
                exit;
            } catch (Exception $e) {
                Session::flash('error', 'Failed to log checkpoint: ' . $e->getMessage());
                header("Location: /admin/progress/create?member_id={$memberId}");
                exit;
            }
        }
    }

    /**
     * Interactive Before & After comparison slider
     */
    public function compare() {
        Auth::requirePermission('members.view');

        $memberId = intval($_GET['member_id'] ?? 0);
        $baseId = !empty($_GET['base_id']) ? intval($_GET['base_id']) : null;
        $latestId = !empty($_GET['latest_id']) ? intval($_GET['latest_id']) : null;

        $member = $this->memberService->getMemberById($memberId);
        if (!$member) {
            Session::flash('error', 'Member not found.');
            header('Location: /admin/members');
            exit;
        }

        $comparison = $this->progressService->getComparisonData($memberId, Auth::user(), $baseId, $latestId);

        View::render('admin/progress/compare', [
            'member' => $member,
            'comparison' => $comparison,
            'baseId' => $baseId,
            'latestId' => $latestId
        ]);
    }
}
