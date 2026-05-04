
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `blood_bags` (
  `bag_id` varchar(50) NOT NULL,
  `blood_type` enum('A+','A-','B+','B-','O+','O-','AB+','AB-') NOT NULL,
  `expiration_date` date NOT NULL,
  `status` enum('Available','Dispatched','Expired','Discarded') DEFAULT 'Available',
  `unit_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `blood_bags` (`bag_id`, `blood_type`, `expiration_date`, `status`, `unit_id`) VALUES
('BAG-69EBC4F567A3D', 'A+', '2026-06-05', 'Dispatched', 'Fridge-A'),
('BAG-69EBC511AA7A0', 'B+', '2026-06-05', 'Available', 'Fridge-B'),
('BAG-69ECC77C96836', 'A-', '2026-06-06', 'Available', 'Fridge-A'),
('BAG-69ECCDADB794E', 'O+', '2026-06-06', 'Available', 'Fridge-A');

CREATE TABLE `donations` (
  `donation_id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `bag_id` varchar(50) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `donation_date` date NOT NULL,
  `volume_ml` int(11) NOT NULL DEFAULT 450
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `donations` (`donation_id`, `donor_id`, `bag_id`, `staff_id`, `donation_date`, `volume_ml`) VALUES
(2, 2, 'BAG-69EBC4F567A3D', 1, '2026-04-24', 450),
(3, 1, 'BAG-69EBC511AA7A0', 1, '2026-04-24', 450),
(6, 1, 'BAG-69ECC77C96836', 1, '2026-04-25', -450),
(10, 6, 'BAG-69ECCDADB794E', 1, '2026-04-25', 450);

CREATE TABLE `donors` (
  `donor_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `blood_type` enum('A+','A-','B+','B-','O+','O-','AB+','AB-') NOT NULL,
  `last_donation_date` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `donors` (`donor_id`, `name`, `phone`, `blood_type`, `last_donation_date`, `user_id`) VALUES
(1, 'Munesh Kumar', '555-0102', 'O+', '2025-11-15', 1),
(2, 'Sohrab', '555-0199', 'A-', '2026-02-10', 2),
(4, 'Atiq d', '878787887', 'A+', NULL, 7),
(6, 'sohrab', '0344404', 'O+', NULL, 12),
(11, 'Atiq d', '232232', 'B+', NULL, 23);

CREATE TABLE `hospitals` (
  `hospital_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `location` varchar(255) NOT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `hospitals` (`hospital_id`, `name`, `location`, `contact_email`, `user_id`) VALUES
(1, 'City General Hospital', '123 Medical Parkway, Downtown', 'admin@citygeneral.com', 4);

CREATE TABLE `requests` (
  `request_id` varchar(50) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `blood_type_required` enum('A+','A-','B+','B-','O+','O-','AB+','AB-') NOT NULL,
  `urgency` enum('Routine','Urgent','Critical') NOT NULL,
  `delivery_location` varchar(150) NOT NULL,
  `contact_person` varchar(100) NOT NULL,
  `status` enum('Pending','Fulfilled','Cancelled') DEFAULT 'Pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `requests` (`request_id`, `hospital_id`, `blood_type_required`, `urgency`, `delivery_location`, `contact_person`, `status`, `request_date`) VALUES
('REQ-2F5B5', 1, 'AB-', 'Urgent', 'dsdsd', 'fsfsfs', 'Pending', '2026-04-25 14:05:11'),
('REQ-48D9E', 1, 'A+', 'Routine', 'ward', 'imdad', 'Fulfilled', '2026-04-24 20:36:24'),
('REQ-495A7', 1, 'A-', 'Urgent', 'sds', 'sdsd', 'Pending', '2026-04-25 12:39:32'),
('REQ-9A385', 1, 'B+', 'Urgent', 'fast', 'sohrab', 'Pending', '2026-04-24 20:40:54'),
('REQ-D823F', 1, 'B+', 'Urgent', 'sasasa', 'sasaa', 'Pending', '2026-04-25 14:26:24');

CREATE TABLE `storage_units` (
  `unit_id` varchar(50) NOT NULL,
  `location` varchar(100) NOT NULL,
  `temperature` decimal(5,2) NOT NULL,
  `status` enum('Optimal','Warning','Critical') DEFAULT 'Optimal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `storage_units` (`unit_id`, `location`, `temperature`, `status`) VALUES
('Fridge-A', 'Main Lab', 4.00, 'Optimal'),
('Fridge-B', 'Surgical Wing', 4.50, 'Optimal');

CREATE TABLE `system_logs` (
  `log_id` int(11) NOT NULL,
  `action_description` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `system_logs` (`log_id`, `action_description`, `user_id`, `log_date`) VALUES
(1, 'System initialized and Super Admin dashboard accessed.', 9, '2026-04-25 13:41:08'),
(2, 'Permanently deleted DONOR account: Atiq ur Rehman (ID: 3).', 9, '2026-04-25 13:46:01'),
(3, 'Created new manager account for sohrab (ID: 10).', 9, '2026-04-25 13:46:23'),
(4, 'Permanently deleted MANAGER account: sohrab (ID: 10).', 9, '2026-04-25 13:57:08'),
(5, 'Permanently deleted DONOR account: sohrab (ID: 11).', 9, '2026-04-25 14:18:42'),
(6, 'Created new manager account for <script>alert(\'Hacked!\');</script> (ID: 13).', 9, '2026-04-25 14:23:49'),
(7, 'Created new staff account for ali (ID: 14).', 9, '2026-04-25 14:30:12'),
(8, 'Created new staff account for Atiq (ID: 15).', 9, '2026-04-25 15:17:50'),
(9, 'Permanently deleted STAFF account: Atiq (ID: 15).', 9, '2026-04-25 15:18:55'),
(10, 'Created new manager account for atiq (ID: 16).', 9, '2026-04-25 15:22:27'),
(11, 'Permanently deleted MANAGER account: atiq (ID: 16).', 9, '2026-04-25 15:22:57'),
(12, 'Created new staff account for sohrab jani (ID: 17).', 9, '2026-04-25 15:24:03'),
(13, 'Permanently deleted STAFF account: sohrab jani (ID: 17).', 9, '2026-04-25 15:24:55'),
(14, 'Permanently deleted DONOR account: Atiq rehman (ID: 18).', 9, '2026-04-25 16:01:53'),
(15, 'Permanently deleted DONOR account: Atiq d (ID: 19).', 9, '2026-04-25 16:07:15'),
(16, 'Permanently deleted DONOR account: Atiq d (ID: 21).', 9, '2026-04-25 16:13:54'),
(17, 'Permanently deleted DONOR account: Atiq d (ID: 22).', 9, '2026-04-25 16:18:43');

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `request_id` varchar(50) NOT NULL,
  `bag_id` varchar(50) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `dispatch_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('donor','staff','manager','hospital','admin') NOT NULL,
  `status` enum('active','deactivated') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password_hash`, `role`, `status`, `created_at`) VALUES
(1, 'Munesh Kumar', 'staff@bloodcare.com', '$2y$10$g1k.K/ZqgD6rI3P.BfA/3.Z.UqZ.Q8.D3.D3.D3.D3.D3.D3.D3.D3', 'staff', 'active', '2026-04-24 18:19:52'),
(2, 'Sohaib Ahmed', 'donor@bloodcare.com', '$2y$10$g1k.K/ZqgD6rI3P.BfA/3.Z.UqZ.Q8.D3.D3.D3.D3.D3.D3.D3.D3', 'donor', 'active', '2026-04-24 18:19:52'),
(4, 'City General Admin', 'admin@citygeneral.com', '$2y$10$g1k.K/ZqgD6rI3P.BfA/3.Z.UqZ.Q8.D3.D3.D3.D3.D3.D3.D3.D3', 'hospital', 'active', '2026-04-24 19:42:31'),
(7, 'Atiq d', 'atiqd400@gmail.com', '$2y$10$0xaN9mGpEHec8vfUvSSa9eSaMnoGUBrEw7jj11/rCFnTgeS/F5qzO', 'donor', 'active', '2026-04-25 13:11:44'),
(8, 'Director Director', 'director@bloodcare.com', '$2y$10$g1k.K/ZqgD6rI3P.BfA/3.Z.UqZ.Q8.D3.D3.D3.D3.D3.D3.D3.D3', 'manager', 'active', '2026-04-25 13:26:30'),
(9, 'Super Admin', 'sysadmin@bloodcare.com', '$2y$10$g1k.K/ZqgD6rI3P.BfA/3.Z.UqZ.Q8.D3.D3.D3.D3.D3.D3.D3.D3', 'admin', 'active', '2026-04-25 13:33:49'),
(12, 'sohrab', 'sohrab@gmail.com', '$2y$10$9YGxBRANn5O7zhML4gLb1OLoE6MHT7ZvTKP4eMO7ClhNkg3WteoOi', 'donor', 'active', '2026-04-25 14:19:13'),
(13, '<script>alert(\'Hacked!\');</script>', 'testing@gmail.com', '$2y$10$/Mc3NIClBPgzDP1Cryk.6ui1.PmFI9SuPcSo6wdrAxcYVOA9GLSkW', 'manager', 'active', '2026-04-25 14:23:49'),
(14, 'ali', 'ali@gmail.com', '$2y$10$Ny7NaP8yBL.AY8x57IuetOQSmFn82pnOhMf8mMgSHsVqTcViZGR2a', 'staff', 'active', '2026-04-25 14:30:12'),
(23, 'Atiq d', 'atiqd300@gmail.com', '$2y$10$WSuJLW1QqBxWUqvBrRrgcu7P57fkPRjGwRVeSFPnOgaUuYAYsIkPC', 'donor', 'active', '2026-04-25 16:19:24');

ALTER TABLE `blood_bags`
  ADD PRIMARY KEY (`bag_id`),
  ADD KEY `unit_id` (`unit_id`);

ALTER TABLE `donations`
  ADD PRIMARY KEY (`donation_id`),
  ADD KEY `donor_id` (`donor_id`),
  ADD KEY `bag_id` (`bag_id`),
  ADD KEY `staff_id` (`staff_id`);

ALTER TABLE `donors`
  ADD PRIMARY KEY (`donor_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `hospitals`
  ADD PRIMARY KEY (`hospital_id`),
  ADD UNIQUE KEY `contact_email` (`contact_email`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `hospital_id` (`hospital_id`);

ALTER TABLE `storage_units`
  ADD PRIMARY KEY (`unit_id`);

ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`log_id`);

ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `bag_id` (`bag_id`),
  ADD KEY `hospital_id` (`hospital_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `donations`
  MODIFY `donation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `donors`
  MODIFY `donor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `hospitals`
  MODIFY `hospital_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `system_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

ALTER TABLE `blood_bags`
  ADD CONSTRAINT `blood_bags_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `storage_units` (`unit_id`) ON DELETE SET NULL;

ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `donors` (`donor_id`),
  ADD CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`bag_id`) REFERENCES `blood_bags` (`bag_id`),
  ADD CONSTRAINT `donations_ibfk_3` FOREIGN KEY (`staff_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `donors`
  ADD CONSTRAINT `donors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `hospitals`
  ADD CONSTRAINT `hospitals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`hospital_id`);

ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`bag_id`) REFERENCES `blood_bags` (`bag_id`),
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`hospital_id`);
COMMIT;

