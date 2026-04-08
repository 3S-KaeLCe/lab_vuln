CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  content VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO messages (content) VALUES
  ('Hello from MySQL'),
  ('POC ready for the security lab');

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  is_admin TINYINT(1) DEFAULT 0,
  avatar_path VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password_hash, is_admin) VALUES
  ('admin', '42b465748f1e67489351a357b8025450', 1),
  ('john', '83b1e2c9872d3177a777cd3b81ba20c0', 0),
  ('doe', '404abd6cf88964e9c62d807ca260f518', 0),
  ('alice', '8c7d6901dc67dada4b9d37c774ca4a89', 0),
  ('bob', '3de1b9caf5f22196132f5416c20d36f1', 0),
  ('cesi', 'd70f1ab775d1c6575a7e03286f884c38', 0),
  ('toto', '21bdad6dad3e547bd514cfae16f72948', 0),
  ('george', '48d0a08e39e94baade4f8a63de585473', 0),
  ('jane', '5702e76fc48a83e6e203f1a11ce72ba9', 0);

CREATE TABLE IF NOT EXISTS resources (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  url VARCHAR(2048) NOT NULL,
  description TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO resources (title, url, description) VALUES
  ('PHP', 'https://www.php.net', 'Site officiel de PHP'),
  ('MySQL', 'https://www.mysql.com', 'Site officiel de MySQL'),
  ('Tailwind CSS', 'https://tailwindcss.com', 'Framework CSS utilitaire');