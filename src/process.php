<?php
    include 'connect.php';

    if(isset($_POST['submit'])){

       
        // Add Member
        if(isset($_POST['form_type']) &&  $_POST['form_type'] == 'add_member'){
            
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);

            $checkQuery = "SELECT * FROM member WHERE name = '$name'";
            $result = mysqli_query($connection, $checkQuery);
            while ($row = mysqli_fetch_assoc($result)) {
                header("Location: setting.php?message=member is already exist!");
                exit();
            } 

            $query = "INSERT INTO member (name, email) VALUES ('$name', '$email')";
            if (mysqli_query($connection, $query)) {
            
            header("Location: setting.php?message=Team member added successfully!&type=member");
            exit();
            } else {
                echo "Error: " . mysqli_error($connection);
            }
            
        }

        // Update Member 
        if(isset($_POST['form_type']) &&  $_POST['form_type'] == 'edit_member'){
            
            $id = $_POST['id']; // make sure it's an integer
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);

            // Prepare update query
            $query = "UPDATE member SET name = ?, email = ? WHERE id = ?";
            $stmt = $connection->prepare($query);

            // Bind parameters: s = string, i = integer
            $stmt->bind_param("ssi", $name, $email, $id);

            // Execute
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: setting.php"); // reload page after update
                exit();
            } else {
                echo "Error updating member: " . $connection->error;
            }
        }

        if(isset($_POST['form_type']) &&  $_POST['form_type'] == 'add_resturant'){
            
            $name = $_POST['name'];

            $query = "INSERT INTO restaurants (name) VALUES ('$name')";
            if (mysqli_query($connection, $query)) {
            
            header("Location: setting.php?message=Resturnt added successfully!&type=resturant");
            exit();
            } else {
                echo "Error: " . mysqli_error($connection);
            }
            
        }

        if(isset($_POST['form_type']) &&  $_POST['form_type'] == 'add_menu_item'){
            
            $name = $_POST['name'];

            $query = "INSERT INTO menu_items (name) VALUES ('$name')";
            if (mysqli_query($connection, $query)) {
            
            header("Location: setting.php?message=Menu added successfully!&type=menu");
            exit();
            } else {
                echo "Error: " . mysqli_error($connection);
            }
            
        }

        if(isset($_POST['form_type']) &&  $_POST['form_type'] == 'add_expense'){

            $menuIds      = isset($_POST['menu']) ? $_POST['menu'] : [];
            $menuJson     = json_encode($menuIds); 
            $restaurantId = $_POST['restaurant'];  // restaurant
            $amount        = $_POST['amount'];      // amount
            $date          = $_POST['date'];        // date
            $paidBy       = $_POST['paidBy'];      // paid by user
            $sharedBy      = isset($_POST['sharedBy']) ? $_POST['sharedBy'] : []; // checkboxes

            $query = "INSERT INTO expenses (menu_id, restaurant_id, amount, date, paid_by) VALUES ('$menuJson', '$restaurantId', '$amount', '$date', '$paidBy')";

            if (mysqli_query($connection, $query)) {
            
                $expenseId = mysqli_insert_id($connection);

                // Insert into expense_shared table for each user in sharedBy
                foreach ($sharedBy as $userId) {
                    $query2 = "INSERT INTO expense_shared (expense_id, user_id) 
                            VALUES ('$expenseId', '$userId')";
                    mysqli_query($connection, $query2);
                }

                header("Location: index.php?message=Expense added successfully!");
                exit();
            } else {
                echo "Error: " . mysqli_error($connection);
            }
        }
    }


    // DELETE MEMBER
    if (isset($_GET['form_type']) && $_GET['form_type'] == 'delete_member' && isset($_GET['id'])) {
        $id = $_GET['id'];

        // Optional: You can use prepared statement for security

        $query = 
        $stmt = $connection->prepare("DELETE FROM member WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: setting.php?message=Member deleted successfully!&type=member");
            exit();
        } else {
            echo "Error deleting member: " . $connection->error;
        }
    }

    if (isset($_GET['form_type']) && $_GET['form_type'] == 'delete_resturant' && isset($_GET['id'])) {
        $id = $_GET['id'];

        // Optional: You can use prepared statement for security

        $query = 
        $stmt = $connection->prepare("DELETE FROM restaurants WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: setting.php?message=Resturant deleted successfully!&type=resturant");
            exit();
        } else {
            echo "Error deleting resturant: " . $connection->error;
        }
    }

    if (isset($_GET['form_type']) && $_GET['form_type'] == 'delete_menu' && isset($_GET['id'])) {
        $id = $_GET['id'];

        // Optional: You can use prepared statement for security

        $query = 
        $stmt = $connection->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: setting.php?message=Menu deleted successfully!&type=menu");
            exit();
        } else {
            echo "Error deleting menu: " . $connection->error;
        }
    }

    // UPDATE STATUS
    if (isset($_GET['form_type']) && $_GET['form_type'] == 'toggle_status' && isset($_GET['id']) && isset($_GET['current_status'])) {

        $id = $_GET['id'];
        $current_status = $_GET['current_status'];

        // Toggle: 1 -> 0, 0 -> 1
        $new_status = ($current_status == 1) ? 0 : 1;

        $stmt = $connection->prepare("UPDATE member SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_status, $id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: setting.php"); // reload page to see changes
            exit();
        } else {
            echo "Error updating status: " . $connection->error;
        }
    }

    // Delete Expense 
    if (isset($_GET['form_type']) && $_GET['form_type'] == 'delete_expense' && isset($_GET['id'])) {
        $expenseId = intval($_GET['id']); // convert to int for safety

        // --- Step 1: Delete related rows from expense_shared ---
        $stmt = $connection->prepare("DELETE FROM expense_shared WHERE expense_id = ?");
        $stmt->bind_param("i", $expenseId);
        if (!$stmt->execute()) {
            echo "Error deleting from expense_shared: " . $stmt->error;
            exit();
        }
        $stmt->close();

        // --- Step 2: Delete the main expense ---
        $stmt = $connection->prepare("DELETE FROM expenses WHERE id = ?");
        $stmt->bind_param("i", $expenseId);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: index.php?message=Expense deleted successfully!");
            exit();
        } else {
            echo "Error deleting expense: " . $stmt->error;
        }
    }

    // UPDATE STATUS
    if (isset($_GET['form_type']) && $_GET['form_type'] == 'send_email' && isset($_GET['id']) && isset($_GET['is_current_send_email'])) {

        $id = $_GET['id'];
        $isCurrentSendEmail = $_GET['is_current_send_email'];

        // Toggle: 1 -> 0, 0 -> 1
        $newSendEmail = ($isCurrentSendEmail == 1) ? 0 : 1;

        $stmt = $connection->prepare("UPDATE member SET is_send_email = ? WHERE id = ?");
        $stmt->bind_param("ii", $newSendEmail, $id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: setting.php"); // reload page to see changes
            exit();
        } else {
            echo "Error updating send email setting : " . $connection->error;
        }
    }

    // SHOW SEND EMAIL BUTTON
    if (isset($_GET['form_type']) && $_GET['form_type'] == 'send_email_button' && isset($_GET['id']) && isset($_GET['show_send_email_button'])) {

        $id = $_GET['id'];
        $isShowSendEmailButton = $_GET['show_send_email_button'];

        // Toggle: 1 -> 0, 0 -> 1
        $newSendEmailButton = ($isShowSendEmailButton == 1) ? 0 : 1;

        $stmt = $connection->prepare("UPDATE settings SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $newSendEmailButton, $id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: setting.php"); // reload page to see changes
            exit();
        } else {
            echo "Error updating send email button setting : " . $connection->error;
        }
    }


?>