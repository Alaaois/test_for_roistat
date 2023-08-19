<?php
require __DIR__ . '/vendor/autoload.php';

use AMO\LeadsAction;

new LeadsAction($_GET['name'], $_GET['price'], $_GET['tel'], $_GET['email']);
