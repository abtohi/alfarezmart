<?php
$_SERVER['REQUEST_URI'] = '/';
define('BASE_PATH', __DIR__ . '/');
require 'app/config/App.php';
require 'app/core/Model.php';
require 'app/core/Database.php';
require 'app/models/SupplierProductModel.php';
require 'app/models/ProductModel.php';
$sp = new SupplierProductModel();
print_r($sp->searchBySupplier(1, 'rokok'));
