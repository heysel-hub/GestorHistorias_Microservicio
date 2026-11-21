-- OPCIONAL: datos de prueba. Ejecutar DESPUÉS de crear las dos bases de datos.
INSERT INTO ms_sprints_db.sprints (nombre, fecha_inicio, fecha_fin) VALUES
 ('Sprint 1 - Inicio', '2026-10-05', '2026-10-19'),
 ('Sprint 2 - Mejoras', '2026-10-20', '2026-11-03');

INSERT INTO ms_historias_db.historias
 (titulo, descripcion, responsable, estado, puntos, fecha_creacion, fecha_finalizacion, sprint_id) VALUES
 ('Login de usuarios', 'Como usuario quiero iniciar sesión para ver mis datos.', 'Ana', 'finalizada', 5, '2026-10-05', '2026-10-12', 1),
 ('Registro de clientes', 'Como administrador quiero registrar clientes nuevos.', 'Luis', 'activa', 3, '2026-10-06', NULL, 1),
 ('Reporte mensual', 'Como gerente quiero un reporte mensual de ventas.', 'Ana', 'impedimento', 8, '2026-10-21', NULL, 2),
 ('Recuperar contraseña', 'Como usuario quiero recuperar mi contraseña por correo.', 'Marta', 'nueva', 2, '2026-10-22', NULL, 2);
