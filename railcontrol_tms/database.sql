CREATE DATABASE IF NOT EXISTS railcontrol_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE railcontrol_db;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(120) UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('Administrator','Dispatcher','Engineer','Inspector') NOT NULL DEFAULT 'Inspector',
  status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE stations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  station_code VARCHAR(12) NOT NULL UNIQUE,
  station_name VARCHAR(120) NOT NULL,
  city VARCHAR(80) NOT NULL,
  platform_count INT NOT NULL DEFAULT 1,
  status ENUM('Active','Maintenance','Closed') NOT NULL DEFAULT 'Active'
);

CREATE TABLE trains (
  id INT AUTO_INCREMENT PRIMARY KEY,
  train_code VARCHAR(20) NOT NULL UNIQUE,
  train_name VARCHAR(100) NOT NULL,
  train_type ENUM('Passenger','Freight','Maintenance') NOT NULL DEFAULT 'Passenger',
  capacity INT NOT NULL DEFAULT 0,
  status ENUM('Available','In Service','Under Maintenance','Out of Service') NOT NULL DEFAULT 'Available',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE routes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  route_code VARCHAR(20) NOT NULL UNIQUE,
  route_name VARCHAR(120) NOT NULL,
  origin_station_id INT NOT NULL,
  destination_station_id INT NOT NULL,
  distance_km DECIMAL(8,2) NOT NULL DEFAULT 0,
  status ENUM('Active','Suspended') NOT NULL DEFAULT 'Active',
  FOREIGN KEY (origin_station_id) REFERENCES stations(id),
  FOREIGN KEY (destination_station_id) REFERENCES stations(id)
);

CREATE TABLE crews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  staff_code VARCHAR(20) NOT NULL UNIQUE,
  full_name VARCHAR(100) NOT NULL,
  position VARCHAR(60) NOT NULL,
  phone VARCHAR(30),
  status ENUM('Available','Assigned','Off Duty') NOT NULL DEFAULT 'Available'
);

CREATE TABLE schedules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  schedule_code VARCHAR(20) NOT NULL UNIQUE,
  train_id INT NOT NULL,
  route_id INT NOT NULL,
  departure_time DATETIME NOT NULL,
  arrival_time DATETIME NOT NULL,
  schedule_status ENUM('Scheduled','Boarding','Departed','Arrived','Delayed','Cancelled') NOT NULL DEFAULT 'Scheduled',
  FOREIGN KEY (train_id) REFERENCES trains(id),
  FOREIGN KEY (route_id) REFERENCES routes(id)
);

CREATE TABLE train_positions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  train_id INT NOT NULL,
  station_id INT NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  movement_status ENUM('On Time','Delayed','Stopped','Moving') NOT NULL DEFAULT 'On Time',
  FOREIGN KEY (train_id) REFERENCES trains(id),
  FOREIGN KEY (station_id) REFERENCES stations(id)
);

CREATE TABLE signals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  signal_code VARCHAR(20) NOT NULL UNIQUE,
  route_id INT NULL,
  location_description VARCHAR(120) NOT NULL,
  signal_aspect ENUM('Red','Yellow','Green') NOT NULL DEFAULT 'Red',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE SET NULL
);

CREATE TABLE maintenance_issues (
  id INT AUTO_INCREMENT PRIMARY KEY,
  issue_code VARCHAR(20) NOT NULL UNIQUE,
  train_id INT NULL,
  station_id INT NULL,
  issue_title VARCHAR(150) NOT NULL,
  description TEXT,
  priority ENUM('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
  issue_status ENUM('Reported','Assigned','In Progress','Resolved') NOT NULL DEFAULT 'Reported',
  reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (train_id) REFERENCES trains(id) ON DELETE SET NULL,
  FOREIGN KEY (station_id) REFERENCES stations(id) ON DELETE SET NULL
);

CREATE TABLE dispatch_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  event_type VARCHAR(80) NOT NULL,
  description TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Demo login: username admin, password password
INSERT INTO users (full_name, username, email, password_hash, role)
VALUES ('System Administrator','admin','admin@railcontrol.local',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Administrator');

INSERT INTO stations (station_code,station_name,city,platform_count) VALUES
('KL','Kuala Lumpur Sentral','Kuala Lumpur',6),
('SG','Seremban','Negeri Sembilan',4),
('JB','JB Sentral','Johor Bahru',5),
('MU','Muar','Johor',2);

INSERT INTO trains (train_code,train_name,train_type,capacity,status) VALUES
('RC101','Express Selatan','Passenger',420,'In Service'),
('RC205','Intercity Timur','Passenger',360,'Available'),
('RC901','Track Inspection Unit','Maintenance',12,'Under Maintenance');

INSERT INTO routes (route_code,route_name,origin_station_id,destination_station_id,distance_km) VALUES
('R-KLJB','Kuala Lumpur – Johor Bahru',1,3,330.00),
('R-KLSG','Kuala Lumpur – Seremban',1,2,65.00),
('R-JBMU','Johor Bahru – Muar',3,4,180.00);

INSERT INTO crews (staff_code,full_name,position,phone) VALUES
('CR001','Amir Hassan','Train Driver','012-3456789'),
('CR002','Siti Aminah','Assistant Driver','012-4567890'),
('CR003','Daniel Lim','Operations Officer','012-5678901');

INSERT INTO schedules (schedule_code,train_id,route_id,departure_time,arrival_time,schedule_status) VALUES
('SCH001',1,1,DATE_ADD(NOW(),INTERVAL 2 HOUR),DATE_ADD(NOW(),INTERVAL 6 HOUR),'Scheduled'),
('SCH002',2,2,DATE_ADD(NOW(),INTERVAL 4 HOUR),DATE_ADD(NOW(),INTERVAL 5 HOUR),'Scheduled');

INSERT INTO signals (signal_code,route_id,location_description,signal_aspect) VALUES
('SIG-KL-01',1,'Kuala Lumpur approach','Green'),
('SIG-JB-02',1,'Johor Bahru entry','Red'),
('SIG-SG-01',2,'Seremban platform approach','Yellow');

INSERT INTO maintenance_issues (issue_code,train_id,issue_title,description,priority,issue_status) VALUES
('ISS001',3,'Brake inspection required','Routine brake system inspection.','High','In Progress'),
('ISS002',1,'Door sensor warning','Intermittent door sensor warning reported.','Medium','Reported');

INSERT INTO train_positions (train_id,station_id,latitude,longitude,movement_status) VALUES
(1,2,2.7297,101.9381,'Moving'),
(2,1,3.1340,101.6860,'On Time');
