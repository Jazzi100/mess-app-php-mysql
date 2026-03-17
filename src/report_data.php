<?php
// report.php
include 'connect.php'; // make sure $connection is set

$startDate = $_GET['startDate'] ?? null;
$endDate   = $_GET['endDate'] ?? null;

// if (!$startDate && !$endDate) {
//     $startDate = date("Y-m-01"); // first day of current month
//     $endDate   = date("Y-m-t");  // last day of current month
// }

if (!$startDate && !$endDate) {
    // Default date range: 11th of current month to 10th of next month
    $currentYear  = date('Y');
    $currentMonth = date('m');

    // Start = 11th of current month
    $startDate = date('Y-m-11');

    // End = 10th of next month
    $nextMonth = date('Y-m', strtotime('+1 month'));
    $endDate   = date("$nextMonth-10");
}

$dateFilter = "";

if ($startDate && $endDate) {
    $dateFilter = "WHERE e.date BETWEEN '" . mysqli_real_escape_string($connection, $startDate) . "' AND '" . mysqli_real_escape_string($connection, $endDate) . "'";
} elseif ($startDate) {
    $dateFilter = "WHERE e.date >= '" . mysqli_real_escape_string($connection, $startDate) . "'";
} elseif ($endDate) {
    $dateFilter = "WHERE e.date <= '" . mysqli_real_escape_string($connection, $endDate) . "'";
}

// --- helper ---
function fmtCents($cents){ return number_format($cents/100, 2); }

// 1) load members (and init arrays)
$members = [];
$balancesCents = [];   // int cents
$totalExpensesCents = [];

$res = mysqli_query($connection, "SELECT id, name FROM member WHERE status = 1 ORDER BY id ASC");
if (!$res) { die("Member query failed: ".mysqli_error($connection)); }
while ($r = mysqli_fetch_assoc($res)) {
    $id = (int)$r['id'];
    $members[$id] = $r['name'];
    $balancesCents[$id] = 0;
    $totalExpensesCents[$id] = 0;
}

// 2) fetch expenses with participants (preserve insertion order by es.id)
$query = "
    SELECT 
        e.id AS expense_id,
        e.amount,
        e.paid_by,
        e.date,
        GROUP_CONCAT(es.user_id ORDER BY es.id) AS shared_member_ids
    FROM expenses e
    LEFT JOIN expense_shared es ON es.expense_id = e.id
    $dateFilter
    GROUP BY e.id
    ORDER BY e.date ASC, e.id ASC
";
$res = mysqli_query($connection, $query);
if (!$res) { die("Expenses query failed: ".mysqli_error($connection)); }

$expenseDetails = []; // optional debug details

while ($row = mysqli_fetch_assoc($res)) {
    $eid = (int)$row['expense_id'];
    $amount = (float)$row['amount'];
    $amountCents = (int) round($amount * 100); // integer cents
    $paidBy = (int)$row['paid_by'];
    $sharedIds = [];
    if (!empty($row['shared_member_ids'])) {
        $sharedIds = array_map('intval', explode(',', $row['shared_member_ids']));
    }

    // if payer id is not in members (edge case), initialize
    if (!isset($balancesCents[$paidBy])) {
        $members[$paidBy] = "Member-".$paidBy;
        $balancesCents[$paidBy] = 0;
        $totalExpensesCents[$paidBy] = 0;
    }

    // Save debug info
    $expenseDetails[$eid] = [
        'id'=>$eid,
        'amount'=>$amount,
        'amount_cents'=>$amountCents,
        'paid_by'=>$paidBy,
        'shared_ids'=>$sharedIds,
    ];

    // if no shared users, treat as payer-only (no split)
    if (count($sharedIds) === 0) {
        $balancesCents[$paidBy] += $amountCents;
        $totalExpensesCents[$paidBy] += $amountCents;
        continue;
    }

    // per-head integer division (cents) and distribute remainder deterministically
    $n = count($sharedIds);
    $perHead = intdiv($amountCents, $n);
    $remainder = $amountCents % $n; // distribute +1 cent to first $remainder participants

    // compute each participant’s share
    foreach ($sharedIds as $idx => $uid) {
        $extra = ($idx < $remainder) ? 1 : 0;
        $shareCents = $perHead + $extra;

        // if ($uid == $paidBy) {
        //     // save actual payer share for later
        //     $payerShareCents = $shareCents;
        //     $expenseDetails[$eid]['shares'][$uid] = "own-share (covered)";
        //     continue;
        // }

        if ($uid == $paidBy) {
            // payer's own share
            $payerShareCents = $shareCents;
            $expenseDetails[$eid]['shares'][$uid] = 0; // store 0 instead of string
            continue;
        }

        // others owe this share
        $balancesCents[$uid] -= $shareCents;
        $expenseDetails[$eid]['shares'][$uid] = $shareCents;
    }

    // payer gets credit of (amount - his actual share)
    $balancesCents[$paidBy] += ($amountCents - $payerShareCents);

    // track total spent by payer
    $totalExpensesCents[$paidBy] += $amountCents;

}

$sql = "SELECT e.id, e.amount, e.paid_by, GROUP_CONCAT(es.user_id) as participants
        FROM expenses e
        JOIN expense_shared es ON e.id = es.expense_id
        $dateFilter
        GROUP BY e.id";
$result = mysqli_query($connection, $sql);

// Step 2: Load expenses into array
$expenses = [];
while ($row = mysqli_fetch_assoc($result)) {
    $expenses[] = [
        'id'          => $row['id'],
        'amount'      => (float)$row['amount'],
        'paid_by'     => $row['paid_by'],
        'participants'=> explode(",", $row['participants'])
    ];
}

$settlements = []; // [from][to] = amount

foreach ($expenses as $exp) {
    $amount = $exp['amount'];
    $paidBy = $exp['paid_by'];
    $participants = $exp['participants'];

    if (count($participants) == 0) continue;
    $perHead = $amount / count($participants);

    foreach ($participants as $uid) {
        if ($uid == $paidBy) continue; // payer ko skip
        // uid owes to paidBy
        $settlements[$uid][$paidBy] = ($settlements[$uid][$paidBy] ?? 0) + $perHead;
    }
    
}


$netSettlements = []; // [from][to] = net amount

foreach ($settlements as $from => $tos) {
    foreach ($tos as $to => $amt) {
        // if reverse exists, subtract
        if (isset($netSettlements[$to][$from])) {
            if ($netSettlements[$to][$from] > $amt) {
                $netSettlements[$to][$from] -= $amt;
            } else {
                $netSettlements[$from][$to] = $amt - $netSettlements[$to][$from];
                unset($netSettlements[$to][$from]);
            }
        } else {
            $netSettlements[$from][$to] = ($netSettlements[$from][$to] ?? 0) + $amt;
        }
    }
}


// 3) Prepare creditors/debtors lists (amounts in cents)
$creditors = []; // receive money (positive)
$debtors = [];   // owe money (positive)
foreach ($balancesCents as $id => $c) {
    if ($c > 0) $creditors[] = ['id'=>$id,'amount'=>$c];
    if ($c < 0) $debtors[]   = ['id'=>$id,'amount'=>abs($c)];
}
// Sort to match large-first (makes fewer transactions)
usort($creditors, function($a,$b){ return $b['amount'] - $a['amount']; });
usort($debtors,   function($a,$b){ return $b['amount'] - $a['amount']; });

// 4) Greedy settlement: debtors pay creditors
$transactions = []; // each: from,to,amount_cents
$matrix = [];
// initialize matrix with all members 0
foreach ($members as $r => $n) {
    foreach ($members as $c => $n2) {
        $matrix[$r][$c] = 0;
    }
}

foreach ($debtors as &$deb) {
    foreach ($creditors as &$cred) {
        if ($deb['amount'] == 0) break;
        if ($cred['amount'] == 0) continue;
        $pay = min($deb['amount'], $cred['amount']);
        if ($pay <= 0) continue;
        $transactions[] = ['from'=>$deb['id'],'to'=>$cred['id'],'amount'=>$pay];
        $matrix[$deb['id']][$cred['id']] += $pay;
        $deb['amount']  -= $pay;
        $cred['amount'] -= $pay;
    }
}
unset($deb,$cred);




// output HTML
$showDebug = isset($_GET['debug']) && $_GET['debug'];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Report - Settlement</title>
  <link rel="icon" type="image/png" href="./meeting.png" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style> pre.debug{background:#f8f9fa;padding:10px;border-radius:6px;} 

    body {
            background: #f9fafc;
        }
        .navbar {
            background: linear-gradient(45deg, #198754, #20c997);
        }
        .navbar .nav-link {
            color: white !important;
            font-weight: 500;
        }
        .navbar .nav-link.active {
            border-bottom: 2px solid yellow;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .form-label {
            font-weight: 500;
        }
        .btn-check + .btn {
            margin: 3px;
        }
        .table thead {
            background: #198754;
            color: white;
        }
        .badge {
            margin: 2px;
        }
  </style>
</head>
<body class="container py-4">

    <!-- NAVBAR -->
      <nav class="navbar navbar-expand-lg mb-4">
        <div class="container-fluid">
          <a class="navbar-brand text-white fw-bold" href="#">Mess Expense</a>
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item">
                <a class="nav-link <?php if($current_page=='index.php'){echo 'active';} ?>" href="index.php">Entry</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?php if($current_page=='report.php'){echo 'active';} ?>" href="report.php">Report</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?php if($current_page=='setting.php'){echo 'active';} ?>" href="setting.php">Settings</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?php if($current_page=='test.php'){echo 'active';} ?>" href="test.php">Testing</a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
      <!-- NAVBAR -->

  
        
        <div class="col">
                <h3 class="mb-4">Expense Settlement Report</h3>
        </div>
        <!-- <div class="col">
            <h4>Filter by Date / Month</h4>
            <form method="get" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="startDate" class="form-label">Start Date</label>
                    <input type="date" id="startDate" name="startDate" class="form-control" value="<?= htmlspecialchars($_GET['startDate'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label for="endDate" class="form-label">End Date</label>
                    <input type="date" id="endDate" name="endDate" class="form-control" value="<?= htmlspecialchars($_GET['endDate'] ?? '') ?>">
                </div>
                <div class="col-md-3 align-self-end">
                    <button type="submit" class="btn btn-success">Filter</button>
                    <a href="report.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div> -->




        
        <form method="get" class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="startDate" class="form-label">Start Date</label>
                <input type="date" id="startDate" name="startDate" class="form-control" 
                    value="<?= htmlspecialchars($startDate) ?>">
            </div>
            <div class="col-md-3">
                <label for="endDate" class="form-label">End Date</label>
                <input type="date" id="endDate" name="endDate" class="form-control" 
                    value="<?= htmlspecialchars($endDate) ?>">
            </div>
            <div class="col-md-3 align-self-end">
                <button type="submit" class="btn btn-success">Filter</button>
                <a href="report.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>








  <div class="mb-3">
    <a class="btn btn-sm btn-outline-secondary" href="?debug=<?= $showDebug ? '0' : '1' ?>">
      <?= $showDebug ? 'Hide debug' : 'Show debug' ?>
    </a>
    <?php
        $result = mysqli_query($connection, "SELECT * FROM settings WHERE name = 'send email button'");
        $row = mysqli_fetch_assoc($result);
  
        if($row['status'] == 1){
            ?>
                <a class="btn btn-sm btn-outline-success" href="send_email.php" >Send Email</a>
            <?php
        }
    ?>
    
  </div>

  

  <?php if ($showDebug): ?>
    <h4>Per-expense breakdown (debug)</h4>
    <pre class="debug">
<?php
foreach ($expenseDetails as $d) {
    echo "Expense #{$d['id']}  amount: ".fmtCents($d['amount_cents'])."  paid_by: ".($members[$d['paid_by']] ?? $d['paid_by'])."\n";
    if (!empty($d['shared_ids'])) {
        echo "  participants: ".implode(', ', $d['shared_ids'])."\n";
        foreach ($d['shares'] as $uid => $shareCents) {
            echo "    - {$members[$uid]} => ".fmtCents($shareCents)."\n";
        }
    } else {
        echo "  (no participants)\n";
    }
    echo "\n";
}
?>
    </pre>
  <?php endif; ?>
