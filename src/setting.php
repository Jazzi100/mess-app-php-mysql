<?php
    include 'connect.php';
    $current_page = basename($_SERVER['PHP_SELF']); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mess Management</title>
  <link rel="icon" type="image/png" href="./meeting.png" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
      body {
          background: #f8f9fa;
      }
      .card {
          box-shadow: 0 4px 8px rgba(0,0,0,0.05);
     
          margin-bottom: 25px;
      }
      .card h4 {
          color: #198754;
          font-weight: 600;
          margin-bottom: 15px;
      }
      .navbar {
       
          margin-bottom: 25px;
      }
      .table th {
          background: #198754;
          color: white;
      }
      .btn {
          border-radius: 6px;
      }
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

  <!-- Navigation -->
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

  <!-- Nav and Tabs -->

  <ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Member</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Resturant</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact-tab-pane" type="button" role="tab" aria-controls="contact-tab-pane" aria-selected="false">Menu</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="advance-tab" data-bs-toggle="tab" data-bs-target="#advance-tab-pane" type="button" role="tab" aria-controls="advance-tab-pane" aria-selected="false">Advance</button>
    </li>
    <!-- <li class="nav-item" role="presentation">
      <button class="nav-link" id="disabled-tab" data-bs-toggle="tab" data-bs-target="#disabled-tab-pane" type="button" role="tab" aria-controls="disabled-tab-pane" aria-selected="false" disabled>Disabled</button>
    </li> -->
  </ul>
  <div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
    <!-- Members Section -->  
    <div class="row mt-5">
      <div class="col-md-6">
      <div class="card p-4">
        <h4>Add Member</h4>
        <?php 
          if(isset($_GET['message']) && $_GET['type'] == "member"){
            ?>
              <span style="font-size:12px; color: red; margin-bottom:10px"><?php echo $_GET['message'] ?></span>
            <?php
          }
        ?>
        <form method="POST" action="process.php" class="row g-2">
          <?php
            $row = null;
            if(isset($_GET['type']) && $_GET['type'] == "edit_member" && isset($_GET['id'])){
              $query = "SELECT * FROM member where id = ".$_GET['id'];
              $result = mysqli_query($connection, $query);
              $row = mysqli_fetch_assoc($result);
            }

            $isEdit = (isset($_GET['type']) && $_GET['type'] == "edit_member");
          
          ?>
          <input type="hidden" name="form_type" value="<?php echo $isEdit ? "edit_member" : "add_member"; ?>">
          <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
          <?php endif; ?>
          <div class="col-12 mb-2">
            <input type="text" class="form-control" name="name" placeholder="Enter member name" required value="<?php echo ($row['name'] ?? ''); ?>" >
          </div>

          <div class="col-12 mb-2">
            <input type="email" class="form-control" name="email" placeholder="Enter member email" value="<?php echo ($row['email'] ?? ''); ?>" >
          </div>
          <div class="col-12">
            <button type="submit" name="submit" class="btn btn-success w-100"><?php echo $isEdit ? "Update Member" : "Add Member"; ?></button>
          </div>
        </form>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card p-4">
        <h4>All Members</h4>
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead>
              <tr>
                <th>S.No</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            <?php
              $result = mysqli_query($connection, "SELECT * FROM member");
              $serial = 1;
              while($row = mysqli_fetch_assoc($result)){ ?>
                <tr>
                  <td><?= $serial++; ?></td>
                  <td><?= $row['name']; ?></td>
                  <td><?= $row['email']; ?></td>
                  <td>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox"
                        <?= ($row['status']==1 ? 'checked' : ''); ?>
                        onclick="window.location.href='process.php?form_type=toggle_status&id=<?= $row['id']; ?>&current_status=<?= $row['status']; ?>'">
                    </div>
                  </td>
                  <td class="text-center">
                    <a href="setting.php?id=<?= $row['id']; ?>&type=edit_member" class="btn btn-sm btn-primary">Edit</a>
                    <a href="process.php?form_type=delete_member&id=<?= $row['id']; ?>" class="btn btn-sm btn-danger">Delete</a>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
    </div>


    <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
       <!-- Restaurants Section -->
        <div class="row mt-5">
          <div class="col-md-6">
            <div class="card p-4">
              <h4>Add Restaurant</h4>
              <form method="POST" action="process.php" class="row g-2">
                <input type="hidden" name="form_type" value="add_resturant">
                <div class="col-8">
                  <input type="text" class="form-control" name="name" placeholder="Enter restaurant name" required>
                  <?php 
                    if(isset($_GET['message']) && $_GET['type'] == "resturant"){
                      ?>
                        <span style="font-size:12px; color: red"><?php echo $_GET['message'] ?></span>
                      <?php
                    }
                  ?>
                </div>
                <div class="col-4">
                  <button type="submit" name="submit" class="btn btn-success w-100">Add</button>
                </div>
              </form>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card p-4">
              <h4>All Restaurants</h4>
              <div class="table-responsive">
                <table class="table table-bordered align-middle">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Name</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                    $result = mysqli_query($connection, "SELECT * FROM restaurants");
                    $serial = 1;
                    while($row = mysqli_fetch_assoc($result)){ ?>
                      <tr>
                        <td><?= $serial++; ?></td>
                        <td><?= $row['name']; ?></td>
                        <td>
                          <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                              <?= ($row['status']==1 ? 'checked' : ''); ?>
                              onclick="window.location.href='process.php?form_type=toggle_status&id=<?= $row['id']; ?>&current_status=<?= $row['status']; ?>'">
                          </div>
                        </td>
                        <td class="text-center">
                          <a href="edit_restaurant.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                          <a href="process.php?form_type=delete_resturant&id=<?= $row['id']; ?>" class="btn btn-sm btn-danger">Delete</a>
                        </td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
    </div>



    <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel" aria-labelledby="contact-tab" tabindex="0">
      <!-- Menu Items Section -->
        <div class="row mt-5">
          <div class="col-md-6">
            <div class="card p-4">
              <h4>Add Menu</h4>
              <form method="POST" action="process.php" class="row g-2">
                <input type="hidden" name="form_type" value="add_menu_item">
                <div class="col-8">
                  <input type="text" class="form-control" name="name" placeholder="Enter menu name" required>
                  <?php
                    if(isset($_GET['message']) && $_GET['type'] == "menu"){
                      ?>
                        <span style="font-size:12px; color: red"><?php echo $_GET['message'] ?></span>
                      <?php
                    }
                  ?>
                </div>
                <div class="col-4">
                  <button type="submit" name="submit" class="btn btn-success w-100">Add</button>
                </div>
              </form>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card p-4">
              <h4>All Menu</h4>
              <div class="table-responsive">
                <table class="table table-bordered align-middle">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Name</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                    $result = mysqli_query($connection, "SELECT * FROM menu_items");
                    $serial = 1;
                    while($row = mysqli_fetch_assoc($result)){ ?>
                      <tr>
                        <td><?= $serial++; ?></td>
                        <td><?= $row['name']; ?></td>
                        <td>
                          <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                              <?= ($row['status']==1 ? 'checked' : ''); ?>
                              onclick="window.location.href='process.php?form_type=toggle_status&id=<?= $row['id']; ?>&current_status=<?= $row['status']; ?>'">
                          </div>
                        </td>
                        <td class="text-center">
                          <a href="edit_menu.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                          <a href="process.php?form_type=delete_menu&id=<?= $row['id']; ?>" class="btn btn-sm btn-danger">Delete</a>
                        </td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
    </div>


    <div class="tab-pane fade" id="advance-tab-pane" role="tabpanel" aria-labelledby="advance-tab" tabindex="0">
      <div class="row mt-5">
        <div class="col-md-12">
            <div class="card p-4">
              <h5>Send email setting</h5>
              <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead>
              <tr>
                <th>S.No</th>
                <th>Name</th>
                <th>Email</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            <?php
              $result = mysqli_query($connection, "SELECT * FROM member");
              $serial = 1;
              while($row = mysqli_fetch_assoc($result)){ ?>
                <tr>
                  <td><?= $serial++; ?></td>
                  <td><?= $row['name']; ?></td>
                  <td><?= $row['email']; ?></td>

                  <td>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox"
                        <?= ($row['is_send_email']==1 ? 'checked' : ''); ?>
                        onclick="window.location.href='process.php?form_type=send_email&id=<?= $row['id']; ?>&is_current_send_email=<?= $row['is_send_email']; ?>'">
                    </div>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
            </div>
          </div>
      </div>

      <div class="row mt-1">
        <div class="col-md-12">
            <div class="card p-4">
              <h5>Other Settings</h5>
              <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead>
              <tr>
                <th>S.No</th>
                <th>Name</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
            <?php
              $result = mysqli_query($connection, "SELECT * FROM settings");
              $serial = 1;
              while($row = mysqli_fetch_assoc($result)){ ?>
                <tr>
                  <td><?= $serial++; ?></td>
                  <td><?= $row['name']; ?></td>
                  <td>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox"
                        <?= ($row['status']==1 ? 'checked' : ''); ?>
                        onclick="window.location.href='process.php?form_type=send_email_button&id=<?= $row['id']; ?>&show_send_email_button=<?= $row['status']; ?>'">
                    </div>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
            </div>
          </div>
      </div>

    </div>
    <!-- <div class="tab-pane fade" id="disabled-tab-pane" role="tabpanel" aria-labelledby="disabled-tab" tabindex="0">...</div> -->
  </div>



  
  

 

  

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
