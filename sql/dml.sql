USE talent_hub2;

INSERT INTO roles (name) VALUES 
('admin'),
('recruiter'),
('candidate');

INSERT INTO categories (name) VALUES 
('Software Development'),
('Marketing'),
('Sales'),
('HR'),
('Finance'),
('Design');

INSERT INTO tags (name) VALUES 
('PHP'),
('JavaScript'),
('MySQL'),
('Remote'),
('Full-time'),
('Senior'),
('Junior'),
('React'),
('Laravel'),
('Python');


INSERT INTO users (name, email, password, role_name) VALUES 
('Admin User', 'admin@talenthub.com', '$2y$12$ocENNKMN1NrnexaRDL3huecXv04oifjQFoY3HUy.K8NiIkGMM5hTm', 'admin'),
('John Recruiter', 'john@techcorp.com', '$2y$12$ocENNKMN1NrnexaRDL3huecXv04oifjQFoY3HUy.K8NiIkGMM5hTm', 'recruiter'),
('Jane Doe', 'jane.candidate@email.com', '$2y$12$ocENNKMN1NrnexaRDL3huecXv04oifjQFoY3HUy.K8NiIkGMM5hTm', 'candidate'),
('Bob Smith', 'bob.smith@email.com', '$2y$12$ocENNKMN1NrnexaRDL3huecXv04oifjQFoY3HUy.K8NiIkGMM5hTm', 'candidate');


INSERT INTO recruiters (id, company_name) VALUES 
(2, 'TechCorp Solutions');


INSERT INTO cvs (path, filename) VALUES 
('/uploads/cvs/3/20260119_160000_jane_cv.pdf', 'jane_cv.pdf'),
('/uploads/cvs/4/20260119_160100_bob_cv.pdf', 'bob_cv.pdf');


INSERT INTO job_offers (title, description, salary, category_name, recruiter_id) VALUES 
('Senior PHP Developer', 'Looking for experienced PHP developer with Laravel...', 65000.00, 'Software Development', 2),
('Frontend React Developer', 'Build modern UIs with React and TypeScript...', 55000.00, 'Software Development', 2),
('Marketing Manager', 'Lead marketing campaigns and strategy...', 70000.00, 'Marketing', 2);


INSERT INTO job_offer_tags (tag_name, job_offer_id) VALUES 
('PHP', 1),
('Laravel', 1),
('Remote', 1),
('React', 2),
('Full-time', 2),
('Senior', 2);


INSERT INTO applications (cv_id, status, user_id, job_offer_id) VALUES 
(1, 'pending', 3, 1),  
(2, 'accepted', 4, 2);