<?php
require __DIR__ . '/vendor/autoload.php';

use Amo\Form;
use Amo\WorkWithToken;

if ((new WorkWithToken())->checkExpires()) {
    new Form();
} else {
    header('Location: /getAccess.php');
}
