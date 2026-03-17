<?php
    include 'connect.php';
    $current_page = basename($_SERVER['PHP_SELF']); 
?>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Multi-select Dropdown</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
  </head>
  <body>
    <div class="container mt-5">
      
      <div class="row mb-3">
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
          <div class="container-fluid">
            
            <div class="collapse navbar-collapse" id="navbarNav">
              <ul class="navbar-nav">
                <li class="nav-item">
                  <a class="nav-link" href="index.php">Entry</a>
                </li>
                <li class="nav-item">
                  <a
                    class="nav-link <?php if($current_page=='report.php'){echo 'active';} ?>"
                    aria-current="page"
                    href=""
                    >Member</a
                  >
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="#">Report</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="setting.php">Settings</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link disabled" aria-disabled="true">Disabled</a>
                </li>
              </ul>
            </div>
          </div>
        </nav>
      </div>
      
        <form method="POST" action="process.php">
            <input type="hidden" name="form_type" value="add_member">
          <div class="row mb-3">
           <?php
                if (isset($_GET['message'])) {
                    $message = htmlspecialchars($_GET['message']); // prevent XSS
                    echo "<div id='alertMessage' class='alert alert-success alert-dismissible fade show' role='alert'>
                            $message
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        </div>";
                }
            ?>
            
            <div class="col">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="col">
                <label for="team" class="form-label">Team</label>
                <select class="form-select" aria-label="Default select example" name="team_id" required>
                <option selected>---Chose any one---</option>
                <?php
                    $query = "SELECT id, name FROM teams";
                    echo $query;
                    print_r($query);

                    $result = mysqli_query($connection, $query);

                    while($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                        
                    <?php }
                ?>
              </select>
            </div>
            <div class="col">
                <label for="name" class="form-label"></label>
                <button type="submit" name="submit" class="btn btn-success">
            Submit
          </button>
            </div>
            
   
            
          </div>

          <br />
        
        </form>
      </div>
      <div class="container">

      <?php


// Fetch all teams
$teamQuery = "SELECT id, name FROM teams";
$teamResult = mysqli_query($connection, $teamQuery);

$teams = [];
if ($teamResult) {
    while ($row = mysqli_fetch_assoc($teamResult)) {
        $teams[] = $row;
    }
}
?>
        <!-- Tab headers -->
  <!-- Tab headers -->
  <ul class="nav nav-tabs" id="teamTab" role="tablist">
    <?php foreach ($teams as $index => $team): ?>
      <li class="nav-item" role="presentation">
        <button class="nav-link <?php echo ($index === 0) ? 'active' : ''; ?>"
                id="team-<?php echo $team['id']; ?>-tab"
                data-bs-toggle="tab"
                data-bs-target="#team-<?php echo $team['id']; ?>"
                type="button"
                role="tab">
          <?php echo htmlspecialchars($team['name']); ?>
        </button>
      </li>
    <?php endforeach; ?>
  </ul>
   
  
  <!-- Tab content -->
  <!-- <div class="tab-content mt-3" id="myTabContent">
    <div class="tab-pane fade show active" id="home" role="tabpanel">
      <h4>Home Data</h4>
      <p>This is the content of the Home tab. You can put a table or form here.</p>
    </div>
    <div class="tab-pane fade" id="profile" role="tabpanel">
      <h4>Profile Data</h4>
      <p>This is the content of the Profile tab. You can show a different dataset here.</p>
    </div>
    <div class="tab-pane fade" id="messages" role="tabpanel">
      <h4>Messages Data</h4>
      <p>This is the content of the Messages tab. Display anything you want here.</p>
    </div>
  </div> -->


<div class="tab-content mt-3" id="teamTabsContent">
    <?php
    // reset teams query for second loop
    $teams = mysqli_query($connection, "SELECT id, name FROM teams");
    $first = true;
    while ($team = mysqli_fetch_assoc($teams)) {
        $teamId = $team['id'];

        // Fetch members of this team
        $members = mysqli_query($connection, "SELECT id, name FROM member WHERE team_id = $teamId");
    ?>
      <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?>" 
           id="team-<?php echo $teamId; ?>" 
           role="tabpanel">
        <h4><?php echo htmlspecialchars($team['name']); ?> Members</h4>

        <?php if (mysqli_num_rows($members) > 0) { ?>
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>ID</th>
                <th>Member Name</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($member = mysqli_fetch_assoc($members)) { ?>
                <tr>
                  <td><?php echo $member['id']; ?></td>
                  <td><?php echo htmlspecialchars($member['name']); ?></td>
                  
                </tr>
              <?php } ?>
            </tbody>
          </table>
        <?php } else { ?>
          <p>No members found for this team.</p>
        <?php } ?>
      </div>
    <?php $first = false; } ?>
  </div>


</div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
