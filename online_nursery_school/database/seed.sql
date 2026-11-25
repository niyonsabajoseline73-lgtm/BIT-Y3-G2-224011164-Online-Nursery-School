CREATE DATABASE IF NOT EXISTS nursery_school;
USE nursery_school;

INSERT INTO users (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@nursery.school', '$2y$10$rKXfHMTq0oZYargMmpqeq.gvrU3d1lw9lH5fVfTh3YbPwosWq32Y.', 'Niyonsaba Joselyne', 'admin'),
('staff1', 'staff1@nursery.school', '$2y$10$rKXfHMTq0oZYargMmpqeq.gvrU3d1lw9lH5fVfTh3YbPwosWq32Y.', 'Aline Mukamana', 'staff'),
('staff2', 'staff2@nursery.school', '$2y$10$rKXfHMTq0oZYargMmpqeq.gvrU3d1lw9lH5fVfTh3YbPwosWq32Y.', 'Emmanuel Nkurunziza', 'staff');

INSERT INTO parents (full_name, phone, email, address) VALUES
('Alice Uwase', '+250788111111', 'alice.uwase@email.com', 'Kigali, Rwanda'),
('Jean Ndayisenga', '+250788222222', 'jean.ndayisenga@email.com', 'Kicukiro, Rwanda'),
('Marie Claire Mutesa', '+250788333333', 'marie.mutesa@email.com', 'Nyarugenge, Rwanda'),
('Patrick Habimana', '+250788444444', 'patrick.habimana@email.com', 'Gasabo, Rwanda');

INSERT INTO users (username, email, password_hash, full_name, role, parent_id) VALUES
('alice', 'alice.uwase@email.com', '$2y$10$rKXfHMTq0oZYargMmpqeq.gvrU3d1lw9lH5fVfTh3YbPwosWq32Y.', 'Alice Uwase', 'parent', 1);

INSERT INTO classes (class_name, teacher_name, description) VALUES
('Little Sprouts', 'Mrs. Mutesa', 'Early learning and sensory play for toddlers.'),
('Rainbow Babies', 'Mrs. Uwineza', 'Creative and social development class.'),
('Sunshine Explorers', 'Mr. Niyitegeka', 'Pre-school readiness and numeracy foundations.');

INSERT INTO pupils (full_name, date_of_birth, class_id, parent_id) VALUES
('Emma Uwase', '2021-03-18', 1, 1),
('Noah Ndayisenga', '2020-11-07', 2, 2),
('Lina Mutesa', '2021-06-22', 3, 3),
('Daniel Habimana', '2020-09-15', 1, 4),
('Grace Uwase', '2022-01-09', 2, 1),
('Leo Ndayisenga', '2021-07-30', 3, 2);

INSERT INTO fees (pupil_id, term, amount_due, amount_paid, payment_status) VALUES
(1, 'Term 1', 150000, 150000, 'Paid'),
(2, 'Term 1', 150000, 120000, 'Partial'),
(3, 'Term 1', 150000, 0.00, 'Pending'),
(4, 'Term 2', 160000, 160000, 'Paid'),
(5, 'Term 2', 160000, 80000, 'Partial'),
(6, 'Term 2', 160000, 0.00, 'Pending');
