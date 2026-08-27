-- Seeder: Demo data with consistent IDs and FK relationships
-- WARNING: This inserts explicit IDs (choose IDs that won't conflict with your existing data)

SET FOREIGN_KEY_CHECKS=0;

-- Demo teachers (5 entries)
INSERT INTO `teacher` (`teacherID`, `meeting_id`, `campusID`, `name`, `designation`, `dob`, `sex`, `religion`, `email`, `phone`, `address`, `jod`, `photo`, `username`, `password`, `usertypeID`, `zoom_api_key`, `zoom_api_secret`, `create_date`, `modify_date`, `create_userID`, `create_username`, `create_usertype`, `active`, `adminID`) VALUES
(100, 0, 1, 'Demo Teacher 1', 'Teacher', '1980-01-01', 'Male', '', 'demo1@example.com', '', '', '2024-01-01', 'default.png', 'demo_teacher1', 'e019b7053c0a364a8d51018e9ba0f6e9d921f2303eb9ff5d077c03ae2cd8c5da6ba20c26acf57f6c2969293957f2189625f204bca4fa8531c073cacbabf571c0', 2, '', '', '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, 1),
(101, 0, 1, 'Demo Teacher 2', 'Teacher', '1981-02-02', 'Female', '', 'demo2@example.com', '', '', '2024-01-01', 'default.png', 'demo_teacher2', 'e019b7053c0a364a8d51018e9ba0f6e9d921f2303eb9ff5d077c03ae2cd8c5da6ba20c26acf57f6c2969293957f2189625f204bca4fa8531c073cacbabf571c0', 2, '', '', '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, 1),
(102, 0, 1, 'Demo Teacher 3', 'Teacher', '1982-03-03', 'Male', '', 'demo3@example.com', '', '', '2024-01-01', 'default.png', 'demo_teacher3', 'e019b7053c0a364a8d51018e9ba0f6e9d921f2303eb9ff5d077c03ae2cd8c5da6ba20c26acf57f6c2969293957f2189625f204bca4fa8531c073cacbabf571c0', 2, '', '', '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, 1),
(103, 0, 1, 'Demo Teacher 4', 'Teacher', '1983-04-04', 'Female', '', 'demo4@example.com', '', '', '2024-01-01', 'default.png', 'demo_teacher4', 'e019b7053c0a364a8d51018e9ba0f6e9d921f2303eb9ff5d077c03ae2cd8c5da6ba20c26acf57f6c2969293957f2189625f204bca4fa8531c073cacbabf571c0', 2, '', '', '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, 1),
(104, 0, 1, 'Demo Teacher 5', 'Teacher', '1984-05-05', 'Male', '', 'demo5@example.com', '', '', '2024-01-01', 'default.png', 'demo_teacher5', 'e019b7053c0a364a8d51018e9ba0f6e9d921f2303eb9ff5d077c03ae2cd8c5da6ba20c26acf57f6c2969293957f2189625f204bca4fa8531c073cacbabf571c0', 2, '', '', '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, 1);

-- Demo classes (5 entries, linked to demo teachers)
INSERT INTO `classes` (`classesID`, `campusID`, `classes`, `classes_numeric`, `teacherID`, `studentmaxID`, `note`, `create_date`, `modify_date`, `create_userID`, `create_username`, `create_usertype`, `adminID`) VALUES
(100, 1, 'Demo Class 1', 1, 100, 999999, 'Demo class 1', '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1),
(101, 1, 'Demo Class 2', 2, 101, 999999, 'Demo class 2', '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1),
(102, 1, 'Demo Class 3', 3, 102, 999999, 'Demo class 3', '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1),
(103, 1, 'Demo Class 4', 4, 103, 999999, 'Demo class 4', '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1),
(104, 1, 'Demo Class 5', 5, 104, 999999, 'Demo class 5', '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1);

-- Demo sections (5 entries, linked to classes)
INSERT INTO `section` (`sectionID`, `section`, `category`, `capacity`, `classesID`, `teacherID`, `note`, `create_date`, `modify_date`, `create_userID`, `create_username`, `create_usertype`, `campusID`, `adminID`) VALUES
(100, 'A', 'Demo', 30, 100, 100, 'Section A', '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, 1),
(101, 'B', 'Demo', 30, 101, 101, 'Section B', '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, 1),
(102, 'C', 'Demo', 30, 102, 102, 'Section C', '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, 1),
(103, 'D', 'Demo', 30, 103, 103, 'Section D', '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, 1),
(104, 'E', 'Demo', 30, 104, 104, 'Section E', '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, 1);

-- Demo parents (5 entries)
INSERT INTO `parents` (`parentsID`, `campusID`, `name`, `father_name`, `mother_name`, `father_profession`, `mother_profession`, `email`, `phone`, `address`, `guardian_realation_with_child`, `guardian_nationality`, `guardian_office_addresss`, `guardian_qualification`, `guardian_cnic`, `guardian_address`, `guardian_phone`, `guardian_profession`, `mother_nationality`, `mother_office_addresss`, `mother_qualification`, `mother_cnic`, `mother_address`, `mother_phone`, `father_nationality`, `father_office_addresss`, `father_qualification`, `father_cnic`, `photo`, `username`, `password`, `usertypeID`, `create_date`, `modify_date`, `create_userID`, `create_username`, `create_usertype`, `active`, `adminID`) VALUES
(100, 1, 'Demo Parent 1', 'Father 1', 'Mother 1', 'Worker', 'Housewife', 'parent1@example.com', '03000000001', 'Address 1', 'Father', 'PK', '', 'Graduate', '11111-1111111-1', '', '', '', 'PK', '', '', '', '', '', '', '', '', '', 'default.png', 'demo_parent1', 'e019b7053c0a364a8d51018e9ba0f6e9d921f2303eb9ff5d077c03ae2cd8c5da6ba20c26acf57f6c2969293957f2189625f204bca4fa8531c073cacbabf571c0', 4, '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, 1),
(101, 1, 'Demo Parent 2', 'Father 2', 'Mother 2', 'Worker', 'Housewife', 'parent2@example.com', '03000000002', 'Address 2', 'Father', 'PK', '', 'Graduate', '22222-2222222-2', '', '', '', 'PK', '', '', '', '', '', '', '', '', '', 'default.png', 'demo_parent2', 'e019b7053c0a364a8d51018e9ba0f6e9d921f2303eb9ff5d077c03ae2cd8c5da6ba20c26acf57f6c2969293957f2189625f204bca4fa8531c073cacbabf571c0', 4, '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, 1),
(102, 1, 'Demo Parent 3', 'Father 3', 'Mother 3', 'Worker', 'Housewife', 'parent3@example.com', '03000000003', 'Address 3', 'Father', 'PK', '', 'Graduate', '33333-3333333-3', '', '', '', 'PK', '', '', '', '', '', '', '', '', '', 'default.png', 'demo_parent3', 'e019b7053c0a364a8d51018e9ba0f6e9d921f2303eb9ff5d077c03ae2cd8c5da6ba20c26acf57f6c2969293957f2189625f204bca4fa8531c073cacbabf571c0', 4, '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, 1),
(103, 1, 'Demo Parent 4', 'Father 4', 'Mother 4', 'Worker', 'Housewife', 'parent4@example.com', '03000000004', 'Address 4', 'Father', 'PK', '', 'Graduate', '44444-4444444-4', '', '', '', 'PK', '', '', '', '', '', '', '', '', '', 'default.png', 'demo_parent4', 'e019b7053c0a364a8d51018e9ba0f6e9d921f2303eb9ff5d077c03ae2cd8c5da6ba20c26acf57f6c2969293957f2189625f204bca4fa8531c073cacbabf571c0', 4, '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, 1),
(104, 1, 'Demo Parent 5', 'Father 5', 'Mother 5', 'Worker', 'Housewife', 'parent5@example.com', '03000000005', 'Address 5', 'Father', 'PK', '', 'Graduate', '55555-5555555-5', '', '', '', 'PK', '', '', '', '', '', '', '', '', '', 'default.png', 'demo_parent5', 'e019b7053c0a364a8d51018e9ba0f6e9d921f2303eb9ff5d077c03ae2cd8c5da6ba20c26acf57f6c2969293957f2189625f204bca4fa8531c073cacbabf571c0', 4, '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, 1);

-- Demo students (5 entries, linked to classes, sections, parents)
INSERT INTO `student` (`studentID`, `name`, `dob`, `sex`, `religion`, `email`, `phone`, `address`, `classesID`, `sectionID`, `roll`, `bloodgroup`, `country`, `registerNO`, `state`, `library`, `hostel`, `transport`, `photo`, `parentID`, `createschoolyearID`, `schoolyearID`, `admission_status`, `education_detail`, `admission_fee`, `registration_fee`, `admission_result`, `emergency_contact_relation`, `emergency_contact_no`, `student_pob`, `monthly_tuttion_fee`, `username`, `password`, `usertypeID`, `create_date`, `modify_date`, `create_userID`, `create_username`, `create_usertype`, `active`, `ethnicity`, `campusID`, `adminID`) VALUES
(100, 'Demo Student 1', '2015-01-01', 'Male', '', 'stud1@example.com', '03000000011', 'Address S1', 100, 100, '1', 'O+', 'PK', 100, '', 0, 0, 0, 'default.png', 100, 1, 1, 'TRUE', '[]', '', '', '', 'Father', '03000000011', '', '', 'demo_student1', 'e019b7053c0a364a8d51018e9ba0f6e9d921f2303eb9ff5d077c03ae2cd8c5da6ba20c26acf57f6c2969293957f2189625f204bca4fa8531c073cacbabf571c0', 3, '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, NULL, 1, 1),
(101, 'Demo Student 2', '2015-02-02', 'Female', '', 'stud2@example.com', '03000000012', 'Address S2', 100, 101, '2', 'A+', 'PK', 101, '', 0, 0, 0, 'default.png', 101, 1, 1, 'TRUE', '[]', '', '', '', 'Mother', '03000000012', '', '', 'demo_student2', 'e019b7053c0a364a8d51018e9ba0f6e9d921f2303eb9ff5d077c03ae2cd8c5da6ba20c26acf57f6c2969293957f2189625f204bca4fa8531c073cacbabf571c0', 3, '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, NULL, 1, 1),
(102, 'Demo Student 3', '2015-03-03', 'Male', '', 'stud3@example.com', '03000000013', 'Address S3', 101, 102, '3', 'B+', 'PK', 102, '', 0, 0, 0, 'default.png', 102, 1, 1, 'TRUE', '[]', '', '', '', 'Father', '03000000013', '', '', 'demo_student3', 'e019b7053c0a364a8d51018e9ba0f6e9d921f2303eb9ff5d077c03ae2cd8c5da6ba20c26acf57f6c2969293957f2189625f204bca4fa8531c073cacbabf571c0', 3, '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, NULL, 1, 1),
(103, 'Demo Student 4', '2015-04-04', 'Female', '', 'stud4@example.com', '03000000014', 'Address S4', 102, 103, '4', 'AB+', 'PK', 103, '', 0, 0, 0, 'default.png', 103, 1, 1, 'TRUE', '[]', '', '', '', 'Mother', '03000000014', '', '', 'demo_student4', 'e019b7053c0a364a8d51018e9ba0f6e9d921f2303eb9ff5d077c03ae2cd8c5da6ba20c26acf57f6c2969293957f2189625f204bca4fa8531c073cacbabf571c0', 3, '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, NULL, 1, 1),
(104, 'Demo Student 5', '2015-05-05', 'Male', '', 'stud5@example.com', '03000000015', 'Address S5', 103, 104, '5', 'O-', 'PK', 104, '', 0, 0, 0, 'default.png', 104, 1, 1, 'TRUE', '[]', '', '', '', 'Father', '03000000015', '', '', 'demo_student5', 'e019b7053c0a364a8d51018e9ba0f6e9d921f2303eb9ff5d077c03ae2cd8c5da6ba20c26acf57f6c2969293957f2189625f204bca4fa8531c073cacbabf571c0', 3, '2024-01-01 00:00:00', '2024-01-01 00:00:00', 1, 'admin', 'Admin', 1, NULL, 1, 1);

-- Demo transport (2 entries) and members linking some students
INSERT INTO `transport` (`transportID`, `campusID`, `route`, `vehicle`, `fare`, `note`, `adminID`) VALUES
(100, 1, 'Demo Route 1', 1, '1000', 'Demo transport 1', 1),
(101, 1, 'Demo Route 2', 2, '1500', 'Demo transport 2', 1);

INSERT INTO `tmember` (`tmemberID`, `studentID`, `transportID`, `name`, `email`, `phone`, `tbalance`, `tjoindate`, `adminID`) VALUES
(100, 100, 100, 'Demo Student 1', 'stud1@example.com', '03000000011', '0.00', '2024-01-01', 1),
(101, 101, 101, 'Demo Student 2', 'stud2@example.com', '03000000012', '0.00', '2024-01-01', 1),
(102, 102, 100, 'Demo Student 3', 'stud3@example.com', '03000000013', '0.00', '2024-01-01', 1);

SET FOREIGN_KEY_CHECKS=1;

-- Notes:
-- - These demo rows use explicit IDs starting at 100 to minimize conflicts with existing data.
-- - Adjust IDs if your target database already contains rows with the same IDs.
