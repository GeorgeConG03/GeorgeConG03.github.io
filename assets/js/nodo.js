require('dotenv').config();
const express = require('express');
const mysql = require('mysql2/promise');
const bcrypt = require('bcryptjs');
const cors = require('cors');

const app = express();

// Middlewares
app.use(cors()); // Habilita CORS para desarrollo
app.use(express.json());

// Conexión a MySQL con manejo correcto del apóstrofe
const pool = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: (process.env.DB_NAME || 'here\'stoyou').replace(/\\'/g, "'"),
    waitForConnections: true,
    connectionLimit: 10,
    namedPlaceholders: true,
    typeCast: function(field, next) {
      if (field.type === 'BIT' && field.length === 1) {
        return field.buffer()[0] === 1;
      }
      return next();
    }
  });
  

// Verificar conexión al iniciar
pool.getConnection()
  .then(conn => {
    console.log('✅ Conectado a MySQL');
    conn.release();
  })
  .catch(err => {
    console.error('❌ Error de conexión a MySQL:', err.message);
    process.exit(1);
  });

// Endpoint de registro mejorado
app.post('/api/registro', async (req, res) => {
  try {
    const { nombre, email, password } = req.body;

    // Validaciones robustas
    if (!nombre || nombre.length < 3) {
      return res.status(400).json({ error: 'Nombre debe tener al menos 3 caracteres' });
    }
    
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      return res.status(400).json({ error: 'Formato de email inválido' });
    }
    
    if (!password || password.length < 6) {
      return res.status(400).json({ error: 'La contraseña debe tener al menos 6 caracteres' });
    }

    // Verificar si el email ya existe
    const [users] = await pool.query('SELECT id FROM usuarios WHERE correo = ?', [email]);
    if (users.length > 0) {
      return res.status(400).json({ error: 'El correo ya está registrado' });
    }

    // Hash de contraseña
    const hashedPassword = await bcrypt.hash(password, 10);

    // Insertar usuario
    const [result] = await pool.execute(
      'INSERT INTO usuarios (nombre, correo, password) VALUES (?, ?, ?)',
      [nombre, email, hashedPassword]
    );

    // Respuesta exitosa
    res.status(201).json({
      success: true,
      id: result.insertId,
      nombre,
      email
    });

  } catch (error) {
    console.error('Error en registro:', error);
    
    // Manejo de errores específicos
    if (error.code === 'ER_NO_SUCH_TABLE') {
      return res.status(500).json({ error: 'La tabla de usuarios no existe' });
    }
    
    res.status(500).json({ error: 'Error interno del servidor' });
  }
});

// Puerto seguro
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`🚀 Servidor corriendo en http://localhost:${PORT}`);
});