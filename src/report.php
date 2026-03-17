
<?php

    include 'report_data.php';
    include 'report_table.php';
?>

<!--  <h3>Total Spent</h3>
  <table class="table table-bordered table-striped mb-4">
    <thead class="table-dark"><tr><th>Member</th><th>Total Spent</th></tr></thead>
    <tbody> 
    <?php foreach ($members as $id => $name): ?>
      <tr><td><?php echo htmlspecialchars($name); ?></td><td><?php echo fmtCents($totalExpensesCents[$id] ?? 0); ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <h3>Net Balances</h3>
  <table class="table table-bordered table-striped mb-4">
    <thead class="table-dark"><tr><th>Member</th><th>Balance</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($members as $id => $name): 
        $c = $balancesCents[$id] ?? 0;
        ?>
      <tr>
        <td><?php echo htmlspecialchars($name); ?></td>
        <td><?php echo fmtCents($c); ?></td>
        <td>
          <?php if ($c > 0): ?>
            <span class="badge bg-success">Will Receive</span>
          <?php elseif ($c < 0): ?>
            <span class="badge bg-danger">Will Pay</span>
          <?php else: ?>
            <span class="badge bg-secondary">Settled</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

 

    <h3>Settlement Transactions (Net)</h3>
    <table class="table table-bordered table-sm">
        <thead class="table-dark">
            <tr>
                <th>From</th>
                <th>To</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach ($netSettlements as $from => $tos) {
            foreach ($tos as $to => $amt) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($members[$from]) . "</td>";
                echo "<td>" . htmlspecialchars($members[$to]) . "</td>";
                echo "<td><span class='badge bg-primary fs-6'>" . number_format($amt, 2) . "</span></td>";
                echo "</tr>";
            }
        }
        ?>
        </tbody>
    </table>

-->

</body>
</html>
