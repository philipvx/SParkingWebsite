<?php
// includes/db_helper.php

/**
 * Menjalankan kueri SELECT dan mengembalikan objek mysqli_result.
 */
function db_query($sql, $params = [], $types = '') {
    global $conn;
    
    if (empty($params)) {
        $result = $conn->query($sql);
        if ($result === false) {
            throw new Exception("Database query failed: " . $conn->error . " | SQL: " . $sql);
        }
        return $result;
    }
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Database prepare failed: " . $conn->error . " | SQL: " . $sql);
    }
    
    if (empty($types)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_double($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
    }
    
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        throw new Exception("Database execute failed: " . $stmt->error . " | SQL: " . $sql);
    }
    
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

/**
 * Mengambil semua baris hasil kueri sebagai array asosiatif.
 */
function db_fetch_all($sql, $params = [], $types = '') {
    $result = db_query($sql, $params, $types);
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

/**
 * Mengambil baris pertama hasil kueri sebagai array asosiatif.
 */
function db_fetch_row($sql, $params = [], $types = '') {
    $result = db_query($sql, $params, $types);
    $row = $result->fetch_assoc();
    return $row ? $row : null;
}

/**
 * Mengambil satu nilai kolom pertama dari baris pertama hasil kueri.
 */
function db_fetch_value($sql, $params = [], $types = '') {
    $row = db_fetch_row($sql, $params, $types);
    if ($row !== null) {
        return reset($row);
    }
    return null;
}

/**
 * Menjalankan kueri INSERT/UPDATE/DELETE.
 */
function db_execute($sql, $params = [], $types = '') {
    global $conn;
    
    if (empty($params)) {
        $result = $conn->query($sql);
        if ($result === false) {
            throw new Exception("Database execution failed: " . $conn->error . " | SQL: " . $sql);
        }
        return true;
    }
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Database prepare failed: " . $conn->error . " | SQL: " . $sql);
    }
    
    if (empty($types)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_double($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
    }
    
    $stmt->bind_param($types, ...$params);
    $success = $stmt->execute();
    if (!$success) {
        throw new Exception("Database execute failed: " . $stmt->error . " | SQL: " . $sql);
    }
    
    $stmt->close();
    return true;
}

/**
 * Mengembalikan ID dari baris terakhir yang dimasukkan (INSERT).
 */
function db_insert_id() {
    global $conn;
    return $conn->insert_id;
}

/**
 * Mengamankan string input dari SQL Injection.
 */
function db_escape($str) {
    global $conn;
    return $conn->real_escape_string($str);
}

/**
 * Memulai transaksi database.
 */
function db_begin_transaction() {
    global $conn;
    $conn->begin_transaction();
}

/**
 * Melakukan commit transaksi database.
 */
function db_commit() {
    global $conn;
    $conn->commit();
}

/**
 * Melakukan rollback transaksi database.
 */
function db_rollback() {
    global $conn;
    $conn->rollback();
}
