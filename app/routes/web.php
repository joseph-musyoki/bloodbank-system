<?php
/** @var Router $router */

// Public
$router->get('/',              [AuthController::class, 'showLogin']);
$router->get('/login',         [AuthController::class, 'showLogin']);
$router->post('/login',        [AuthController::class, 'login']);
$router->get('/register',      [AuthController::class, 'showRegister']);
$router->post('/register',     [AuthController::class, 'register']);
$router->get('/logout',        [AuthController::class, 'logout']);

// Donor Portal
$router->get('/donor/dashboard',         [DonorController::class, 'dashboard']);
$router->get('/donor/appointments',      [DonorController::class, 'appointments']);
$router->get('/donor/appointments/book', [DonorController::class, 'bookAppointment']);
$router->post('/donor/appointments/book',[DonorController::class, 'storeAppointment']);
$router->get('/donor/history',           [DonorController::class, 'history']);
$router->get('/donor/profile',           [DonorController::class, 'profile']);
$router->post('/donor/profile',          [DonorController::class, 'updateProfile']);

// Staff Portal
$router->get('/staff/dashboard',              [StaffController::class, 'dashboard']);
$router->get('/staff/inventory',              [StaffController::class, 'inventory']);
$router->post('/staff/inventory/expire',      [StaffController::class, 'expireUnits']);
$router->get('/staff/donors',                 [StaffController::class, 'donors']);
$router->get('/staff/donors/:id',             [StaffController::class, 'donorDetail']);
$router->get('/staff/donors/:id/donate',      [StaffController::class, 'recordDonation']);
$router->post('/staff/donors/:id/donate',     [StaffController::class, 'storeDonation']);
$router->post('/staff/donors/:id/defer',      [StaffController::class, 'deferDonor']);
$router->get('/staff/requests',               [StaffController::class, 'requests']);
$router->get('/staff/requests/:id',           [StaffController::class, 'requestDetail']);
$router->post('/staff/requests/:id/fulfill',  [StaffController::class, 'fulfillRequest']);

// Hospital Portal
$router->get('/hospital/dashboard',           [HospitalController::class, 'dashboard']);
$router->get('/hospital/request',             [HospitalController::class, 'showRequestForm']);
$router->post('/hospital/request',            [HospitalController::class, 'submitRequest']);
$router->get('/hospital/requests',            [HospitalController::class, 'myRequests']);
$router->get('/hospital/requests/:id',        [HospitalController::class, 'requestStatus']);
$router->post('/hospital/requests/:id/cancel',[HospitalController::class, 'cancelRequest']);