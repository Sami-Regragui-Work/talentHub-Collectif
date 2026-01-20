CREATE DATABASE IF NOT EXISTS talent_hub2;
USE talent_hub2;
CREATE TABLE `users` (
    `id` int PRIMARY KEY AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `email` varchar(150) UNIQUE NOT NULL,
    `password` varchar(255) NOT NULL,
    `created_at` datetime DEFAULT(CURRENT_TIMESTAMP),
    `role_name` enum(
        "admin",
        "recruiter",
        "candidate"
    ) NOT NULL
);
CREATE TABLE `roles` (
    `name` enum(
        "admin",
        "recruiter",
        "candidate"
    ) PRIMARY KEY
);

CREATE TABLE `recruiters` (
    `id` int PRIMARY KEY,
    `company_name` varchar(150) NOT NULL
);

CREATE TABLE `categories` ( `name` varchar(100) PRIMARY KEY );

CREATE TABLE `tags` ( `name` varchar(100) PRIMARY KEY );

CREATE TABLE `job_offers` (
    `id` int PRIMARY KEY AUTO_INCREMENT,
    `title` varchar(150) NOT NULL,
    `description` text NOT NULL,
    `salary` decimal(10, 2),
    `is_archived` boolean DEFAULT false,
    `created_at` datetime DEFAULT(CURRENT_TIMESTAMP),
    `category_name` varchar(100) NOT NULL,
    `recruiter_id` int NOT NULL
);

CREATE TABLE `applications` (
    `id` int PRIMARY KEY AUTO_INCREMENT,
    `cv_id` int,
    `status` enum(
        "pending",
        "accepted",
        "rejected"
    ) DEFAULT 'pending',
    `user_id` int NOT NULL,
    `job_offer_id` int NOT NULL,
    `applied_at` datetime DEFAULT(CURRENT_TIMESTAMP)
);

CREATE TABLE `cvs` (
    `id` int PRIMARY KEY AUTO_INCREMENT,
    `path` varchar(500),
    `filename` varchar(75)
);

CREATE TABLE `job_offer_tags` (
    `id` int PRIMARY KEY AUTO_INCREMENT,
    `tag_name` varchar(100),
    `job_offer_id` int
);

ALTER TABLE `users`
ADD FOREIGN KEY (`role_name`) REFERENCES `roles` (`name`);

ALTER TABLE `recruiters`
ADD FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `job_offers`
ADD FOREIGN KEY (`category_name`) REFERENCES `categories` (`name`);

ALTER TABLE `job_offers`
ADD FOREIGN KEY (`recruiter_id`) REFERENCES `recruiters` (`id`) ON DELETE CASCADE;

ALTER TABLE `applications`
ADD FOREIGN KEY (`cv_id`) REFERENCES `cvs` (`id`) ON DELETE SET NULL;

ALTER TABLE `applications`
ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `applications`
ADD FOREIGN KEY (`job_offer_id`) REFERENCES `job_offers` (`id`) ON DELETE CASCADE;

ALTER TABLE `job_offer_tags`
ADD FOREIGN KEY (`tag_name`) REFERENCES `tags` (`name`) ON DELETE CASCADE;

ALTER TABLE `job_offer_tags`
ADD FOREIGN KEY (`job_offer_id`) REFERENCES `job_offers` (`id`) ON DELETE CASCADE;