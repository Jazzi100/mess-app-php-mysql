<?php
    include 'connect.php';
    $current_page = basename($_SERVER['PHP_SELF']);

   
    
    $startDate = $_GET['startDate'] ?? null;
    $endDate   = $_GET['endDate'] ?? null;
    $searchBy   = $_GET['searchBy'] ?? null;

    echo  $searchBy;
    // If no date filter provided, default to current month
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

    if($searchBy){
      $searchBy = (int)$searchBy;
      $dateFilter = "WHERE e.paid_by = '" . mysqli_real_escape_string($connection, $searchBy) . "'";
    }


    // $serchLike = "";
    // if (!empty($searchBy)) {

    // // force integer (security)
    // $searchBy = (int)$searchBy;

    // $serchLike = "e.paid_by = $searchBy";

    // echo "<br/>";

    // // $query = "SELECT e.* FROM expenses e  WHERE $serchLike AND   ORDER BY e.date DESC";

    // $query = "SELECT e.* FROM expenses e $dateFilter AND $serchLike ORDER BY e.date DESC";

    // echo $query;
    // $result = mysqli_query($connection, $query);

?>

<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Expense Manager</title>
     <link rel="icon" type="image/png" href="./meeting.png" />

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
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
  <body>
    <div class="container mt-4">

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
       
      

      <!-- EXPENSE ENTRY FORM -->
      <div class="card mb-4">
        <div class="card-header bg-success text-white fw-bold">Add Expense</div>
        <div class="card-body">
          <form method="POST" action="process.php">
            <input type="hidden" name="form_type" value="add_expense">
            <div class="row g-3">
              
              <!-- Menu -->
              <!-- <div class="col-md-3">
                <label for="menu" class="form-label">Menu</label>
                <select class="form-select select2" name="menu[]" id="menu" multiple required>
                  <?php
                    $query = "SELECT id, name FROM menu_items WHERE status = 1 ORDER BY name ASC";
                    $result = mysqli_query($connection, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                    }
                  ?>
                </select>
              </div> -->

               <div class="col">
                <label for="menu" class="form-label">Menu</label>
                <select class="form-select select2" name="menu[]" id="menu" multiple required>
                    <?php
                    // Fetch active menu items
                    $query = "SELECT id, name FROM menu_items WHERE status = 1 ORDER BY name ASC";
                    $result = mysqli_query($connection, $query);

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                    }
                    ?>
                </select>
            </div>

              <!-- Restaurant -->
              <div class="col-md-3">
                <label for="restaurant" class="form-label">Restaurant</label>
                <select class="form-select" name="restaurant" required>
                  <option value="">-- select one --</option>
                  <?php
                    $query = "SELECT id, name FROM restaurants WHERE status = 1 ORDER BY name ASC";
                    $result = mysqli_query($connection, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                    }
                  ?>
                </select>
              </div>

              <!-- Amount -->
              <div class="col-md-2">
                <label class="form-label">Amount</label>
                <input type="text" class="form-control" name="amount" required>
              </div>

              <!-- Date -->
              <div class="col-md-2">
                <label class="form-label">Date</label>
                <input type="date" class="form-control" name="date" value="<?php echo date('Y-m-d'); ?>" required>
              </div>
             

              <!-- Paid By -->
              <div class="col-md-2">
                <label for="paidBy" class="form-label">Paid By</label>
                <select class="form-select" name="paidBy" required>
                  <option value="">-- select member --</option>
                  <?php
                    $query = "SELECT id, name FROM member WHERE status = 1 ORDER BY name ASC";
                    $result = mysqli_query($connection, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                    }
                  ?>
                </select>
              </div>
            </div>

            <!-- Shared By -->
            <div class="mt-4">
              <label class="form-label">Shared By</label>
              <div class="d-flex flex-wrap">
                <?php
                  $query = "SELECT id, name FROM member WHERE status = 1 ORDER BY name ASC";
                  $result = mysqli_query($connection, $query);
                  while ($row = mysqli_fetch_assoc($result)) {
                      $memberId = $row['id'];
                      $memberName = $row['name'];
                      echo '
                        <input type="checkbox" class="btn-check" id="member'.$memberId.'" 
                          name="sharedBy[]" value="'.$memberId.'" autocomplete="off">
                        <label class="btn btn-outline-success" for="member'.$memberId.'">'.$memberName.'</label>
                      ';
                  }
                ?>
              </div>
            </div>

            <div class="mt-4">
              <button type="submit" name="submit" class="btn btn-success px-4">💾 Save Expense</button>
            </div>
          </form>
        </div>
      </div>

    <!-- DATE FILTER -->
    <form method="get" class="row g-3 mb-4">
        <div class="col-md-2">
            <label for="startDate" class="form-label">Start Date</label>
            <input type="date" id="startDate" name="startDate" class="form-control" 
                value="<?= htmlspecialchars($startDate) ?>">
        </div>
        <div class="col-md-2">
            <label for="endDate" class="form-label">End Date</label>
            <input type="date" id="endDate" name="endDate" class="form-control" 
                value="<?= htmlspecialchars($endDate) ?>">
        </div>

        <!-- Paid By -->
        <div class="col-md-2">
          <label for="searchBy" class="form-label">Search By</label>
          <select class="form-select" name="searchBy">
            <option value="">-- select member --</option>
            <?php
              $query = "SELECT id, name FROM member WHERE status = 1 ORDER BY name ASC";
              $result = mysqli_query($connection, $query);
              while ($row = mysqli_fetch_assoc($result)) {
                  echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
              }
            ?>
          </select>
        
      </div>
        <div class="col-md-2 align-self-end">
            <button type="submit" class="btn btn-success">Filter</button>
            <a href="index.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>
    <!-- DATE FILTER -->

      <!-- EXPENSE LIST -->
      <div class="card">
        <div class="card-header bg-success text-white fw-bold">Expense Records</div>
        <div class="card-body">
                  
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>S.No</th>
                <th>Date</th>
                <th>Restaurant</th>
                <th>Menu Items</th>
                <th>Shared By</th>
                <th>Paid By</th>
                <th>Amount</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $query = "
                  SELECT e.id, e.date, e.menu_id, e.restaurant_id, r.name AS restaurant_name,
                         e.amount, payer.name AS paid_by,
                         (
                           SELECT GROUP_CONCAT(m.name ORDER BY m.name ASC SEPARATOR ', ')
                           FROM menu_items m
                           WHERE JSON_CONTAINS(e.menu_id, CONCAT('\"', m.id, '\"'))
                         ) AS menu_names,
                         GROUP_CONCAT(DISTINCT es.user_id) AS shared_users_ids
                  FROM expenses e
                  LEFT JOIN restaurants r ON r.id = e.restaurant_id
                  LEFT JOIN member payer ON payer.id = e.paid_by
                  LEFT JOIN expense_shared es ON es.expense_id = e.id
                  $dateFilter
                  GROUP BY e.id ORDER BY e.id DESC";
                
                $result = mysqli_query($connection, $query);
                $serial = 1;
                $total = 0;
                while($row = mysqli_fetch_assoc($result)){
                  ?><tr><?php
                  ?><td><?php echo $serial; ?></td><?php
                  ?><td><?php echo date("d-m-Y", strtotime($row['date'])); ?></td><?php
                  ?><td><?php echo $row['restaurant_name']; ?></td><?php
                  ?><td><?php echo $row['menu_names']; ?></td><?php
                  ?>
                    <td>
                      <?php
                        $ids = $row['shared_users_ids'];
                        if($ids){
                          $queryMembers = "SELECT name FROM member WHERE id IN ($ids)";
                          $resMembers = mysqli_query($connection, $queryMembers);
                          while($m = mysqli_fetch_assoc($resMembers)) {
                            echo "<span class='badge bg-info text-dark fs-7'>".$m['name']."</span> ";
                          }
                        }
                      ?>
                    </td>
                  <?php
                  ?><td><?php echo $row['paid_by']; ?></td><?php
                  ?><td><?php echo $row['amount']; ?></td><?php
                  ?><td><button class='btn btn-sm btn-danger' onclick='confirmDelete("<?php echo $row["id"]; ?>")'>Delete</button></td><?php
                  ?></tr><?php
                  $serial++;
                  $total = $total+$row['amount'];
                }
                  ?>
                    <tr>
                      <td colspan="6" align="center"><b></b></td>
                      <td><b style="color:green; font-size:20px"><?php echo $total; ?></b></td>
                      <td><b style="color:green; font-size:20px">Total</b></td>
                    </tr>
                  <?php
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#menu').select2({
                placeholder: "-- Select Menu Items --",
                allowClear: true,
                width: '100%'
            });
        });

        function confirmDelete(id) {
            Swal.fire({
                title: "Are you sure?",
                text: "This will permanently delete the expense record!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                // Redirect to PHP deletion URL
                window.location.href = "process.php?form_type=delete_expense&id=" + id;
                }
            });
        }
    </script>
  </body>
</html>
