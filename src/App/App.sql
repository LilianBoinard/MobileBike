-- ============================================
-- LDD MobileBike
-- ============================================

CREATE TABLE User_(
                      id INT AUTO_INCREMENT,
                      username VARCHAR(50) NOT NULL,
                      email VARCHAR(100) NOT NULL,
                      password VARCHAR(255) NOT NULL,
                      created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      profile_image VARCHAR(300),
                      PRIMARY KEY(id),
                      UNIQUE(username),
                      UNIQUE(email)
);

-- Table des rôles
CREATE TABLE Role_(
                      id INT AUTO_INCREMENT,
                      role_name VARCHAR(50) NOT NULL UNIQUE,
                      PRIMARY KEY(id)
);

-- Table de liaison utilisateur-rôle (many-to-many)
CREATE TABLE User_Role(
                          user_id INT,
                          role_id INT,
                          PRIMARY KEY(user_id, role_id),
                          FOREIGN KEY fk_user_role_user (user_id) REFERENCES User_(id)
                              ON DELETE CASCADE ON UPDATE CASCADE,
                          FOREIGN KEY fk_user_role_role (role_id) REFERENCES Role_(id)
                              ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Product(
                        id INT AUTO_INCREMENT,
                        name VARCHAR(100) NOT NULL,
                        short_description VARCHAR(200) NOT NULL,
                        long_description VARCHAR(3000),
                        price DECIMAL(15,2) NOT NULL,
                        stock_quantity INT NOT NULL DEFAULT 0,
                        brand VARCHAR(100),
                        image VARCHAR(300) NOT NULL,
                        PRIMARY KEY(id)
);

CREATE TABLE MobileBike(
                           product_id INT,
                           color VARCHAR(50),
                           material VARCHAR(50),
                           PRIMARY KEY(product_id),
                           FOREIGN KEY fk_mobilebike_product (product_id) REFERENCES Product(id)
                               ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Used(
                     product_id INT,
                     PRIMARY KEY(product_id),
                     FOREIGN KEY fk_used_mobilebike (product_id) REFERENCES MobileBike(product_id)
                         ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Trikes(
                       product_id INT,
                       PRIMARY KEY(product_id),
                       FOREIGN KEY fk_trikes_mobilebike (product_id) REFERENCES MobileBike(product_id)
                           ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Recumbent(
                          product_id INT,
                          PRIMARY KEY(product_id),
                          FOREIGN KEY fk_recumbent_mobilebike (product_id) REFERENCES MobileBike(product_id)
                              ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Fairing(
                        product_id INT,
                        PRIMARY KEY(product_id),
                        FOREIGN KEY fk_fairing_mobilebike (product_id) REFERENCES MobileBike(product_id)
                            ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Special(
                        product_id INT,
                        PRIMARY KEY(product_id),
                        FOREIGN KEY fk_special_mobilebike (product_id) REFERENCES MobileBike(product_id)
                            ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Spare_Part(
                           product_id INT,
                           PRIMARY KEY(product_id),
                           FOREIGN KEY fk_sparepart_product (product_id) REFERENCES Product(id)
                               ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Order_(
                       id INT AUTO_INCREMENT,
                       number INT NOT NULL,
                       date_ DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                       user_id INT NOT NULL,
                       PRIMARY KEY(id),
                       FOREIGN KEY fk_order_user (user_id) REFERENCES User_(id)
                           ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE Appointment(
                            id INT AUTO_INCREMENT,
                            address VARCHAR(50) NOT NULL,
                            date_ DATE NOT NULL,
                            message VARCHAR(50) NOT NULL,
                            user_id INT NOT NULL,
                            PRIMARY KEY(id),
                            UNIQUE(user_id),
                            FOREIGN KEY fk_appointment_user (user_id) REFERENCES User_(id)
                                ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Content(
                        id VARCHAR(50),
                        name VARCHAR(150) NOT NULL,
                        PRIMARY KEY(id),
                        UNIQUE(name)
);

CREATE TABLE Resource(
                         id VARCHAR(50),
                         name VARCHAR(200) NOT NULL,
                         url VARCHAR(50) NOT NULL,
                         PRIMARY KEY(id),
                         UNIQUE(name),
                         UNIQUE(url)
);

CREATE TABLE LandingPage(
                            content_id VARCHAR(50),
                            hero_title VARCHAR(150) NOT NULL,
                            hero_description VARCHAR(300) NOT NULL,
                            PRIMARY KEY(content_id),
                            FOREIGN KEY fk_landingpage_content (content_id) REFERENCES Content(id)
                                ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE Article(
                        content_id VARCHAR(50),
                        title VARCHAR(150) NOT NULL,
                        description VARCHAR(300) NOT NULL,
                        content VARCHAR(50) NOT NULL,
                        date_ DATE NOT NULL,
                        PRIMARY KEY(content_id),
                        UNIQUE(title),
                        FOREIGN KEY fk_article_content (content_id) REFERENCES Content(id)
                            ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE OrderItem(
                          product_id INT,
                          order_id INT,
                          quantity INT NOT NULL DEFAULT 1,
                          PRIMARY KEY(product_id, order_id),
                          FOREIGN KEY fk_orderitem_product (product_id) REFERENCES Product(id)
                              ON DELETE RESTRICT ON UPDATE CASCADE,
                          FOREIGN KEY fk_orderitem_order (order_id) REFERENCES Order_(id)
                              ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE ResourceItem(
                             user_id INT,
                             resource_id VARCHAR(50),
                             PRIMARY KEY(user_id, resource_id),
                             FOREIGN KEY fk_resourceitem_user (user_id) REFERENCES User_(id)
                                 ON DELETE CASCADE ON UPDATE CASCADE,
                             FOREIGN KEY fk_resourceitem_resource (resource_id) REFERENCES Resource(id)
                                 ON DELETE CASCADE ON UPDATE CASCADE
);

-- ============================================
-- RÔLES
-- ============================================

INSERT INTO Role_ (role_name) VALUES
                                  ('CLIENT'),
                                  ('ADMINISTRATOR')