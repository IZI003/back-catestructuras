<?php

/*
CREATE TABLE carrito (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  estado ENUM('abierto', 'cerrado', 'pagado') DEFAULT 'abierto',
  total DECIMAL(10,2) DEFAULT 0
);



CREATE TABLE carrito_detalle (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_carrito INT NOT NULL,
  id_producto INT NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (id_carrito) REFERENCES carrito(id),
  FOREIGN KEY (id_producto) REFERENCES producto(id)
);

*/