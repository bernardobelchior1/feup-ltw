<?php
include_once('../../database/restaurants.php');
include_once('../utils/utils.php');

session_start(['cookie_httponly' => true]);

// Check if the user comes from the Add Restaurant page.
if ($_SESSION['token'] !== $_POST['token']) {
    header('HTTP/1.0 403 Forbidden');
    header('Location: ../index.php?page=403.html');
    die();
}

$name = htmlspecialchars($_POST['name']);
$address = htmlspecialchars($_POST['address']);
$description = htmlspecialchars($_POST['description']);

//Sets the owner. If no owner is set, the owner is the user signed in.
$ownerId = isset($_SESSION['ownerId']) ? $_SESSION['ownerId'] : $_SESSION['userId'];

if (isset($name) && isset($address)) {
    $result = addRestaurant($ownerId, $name, $address, $description);

    if ($result === null) { //If an error ocurred, store the error message.
        $_SESSION['addRestaurantError'] = $result[2];
    } else {
        $id = getRestaurantByName($name);

        if(!file_exists('../../restaurant_pictures/' . $id))
            mkdir('../../restaurant_pictures/' . $id, 0777, true);

        for ($i = 0; $i < count($_FILES['photos']['name']); $i++) {
            $extension = pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION);
            $picturePath = 'restaurant_pictures/' . $id . '/' . $i . '.' . $extension;
            move_uploaded_file($_FILES['photos']['tmp_name'][$i], '../../' . $picturePath);
            addPhoto($id, $picturePath);
        }
    }
}

$_SESSION['token'] = generateRandomToken();
header('Location: ../index.php?page=profile.php&id=' . $_SESSION['userId']);
die();
