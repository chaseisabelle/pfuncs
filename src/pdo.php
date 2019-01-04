<?php
/**
 * start a pdo transaction
 *
 * @param resource $pdo is teh pdo connection resource
 * @return resource
 */
function pdo_begin($pdo) {
    if (!$pdo->beginTransaction()) {
        pfunc_error('Failed to start PDO transaction for ' . spy($pdo) . '.');
    }

    return $pdo;
}

/**
 * get a single cell from a table
 *
 * @param resource $pdo is the connection
 * @param string $request is the sql request
 * @param mixed[] $params is the array of params to fill in
 * @param mixed $default is teh value to return if empty result set
 * @return mixed the value in the cell or the default
 */
function pdo_cell($pdo, $request, $params = [], $default = null) {
    $response = pdo_row($pdo, $request, $params);

    if (!is_array($response)) {
        return $response;
    }

    if (!$response && func_num_args() > 3) {
        return $default;
    }

    return array_first($response);
}

/**
 * closes pdo conncetion
 *
 * @param PDO the pdo object
 */
function pdo_close(&$pdo) {
    destroy($pdo);
}

/**
 * get a column of values
 *
 * @param resource $pdo is the connection
 * @param string $request is the sql request
 * @param mixed[] $params is the list of params for the request
 * @param mixed $default is teh value to return if empty result set
 * @return mixed the column of values or the default
 */
function pdo_column($pdo, $request, $params = [], $default = null) {
    $response = pdo_query($pdo, $request, $params);

    if (!is_array($response)) {
        return $response;
    }

    if (!$response && func_num_args() > 3) {
        return $default;
    }

    foreach ($response as $key => $row) {
        $response[$key] = array_first($row);
    }

    return $response;
}

/**
 * commit a transaction
 *
 * @param resource $pdo is teh conneciton
 * @return resource
 */
function pdo_commit($pdo) {
    if (!$pdo->commit()) {
        pfunc_error('Failed to commit PDO transaction for ' . spy($pdo) . '.');
    }

    return $pdo;
}

/**
 * check if value exists in database
 *
 * <code>
 * pdo_exists($pdo, 'SELECT id FROM users WHERE name = ? LIMIT 1', ['john']); //<< check if there is a user named john
 * </code>
 *
 * @param resource $pdo is the connection
 * @param string $request is the sql request
 * @param mixed[] $params is the query params
 * @return bool true if value exists, false elsewise
 */
function pdo_exists($pdo, $request, $params = []) {
    return boolval(pdo_query($pdo, $request, $params));
}

/**
 * opens a pdo connect to a database
 *
 * @param string $name is the db type (i.e. mysql)
 * @param string $host is the host
 * @param int $port is the poirt
 * @param string $user is the username
 * @param string $pass is the poassword
 * @param string $schema database name
 * @return PDO object instance
 */
function pdo_open($driver, $host, $port, $user, $pass, $schema = null) {
    switch ($driver) {
        case 'mysql':
            $dsn = 'mysql:host=' . $host . ';port=' . $port . ';';

            break;
        default:
            trigger_error('Invalid or unsupported PDO driver ' . spy($driver) . '.');
    }

    $pdo = new PDO($dsn, $user, $pass);

    if (!$pdo) {
        pfunc_error('Failed to connect PDO.');
    }

    if ($schema) {
        pdo_use($pdo, $schema);
    }

    return $pdo;
}

/**
 * creates a list of ?s for multiple params
 *
 * <code>
 * var_dump(pdo_qmarks(3)); //<< outputs: ?, ?, ?
 * </code>
 *
 * @param int $count is the number of ?s to create
 * @return string
 */
function pdo_qmarks($count) {
    return $count ? str_repeat('?, ', (is_array($count) ? count($count) : $count) - 1) . '?' : '';
}

/**
 * pefroms query on pdp
 *
 * @param PDO $pdo is th pdo connection
 * @param mixed $request is the request (if array is submitted then transaction is attempted)
 * @param array $params is the list of values to sanitize and inject into request (defaulkts to empty array)
 * @return mixed assoc array upon select, id upon insert, or number of affected rows upon update/delete
 */
function pdo_query($pdo, $request, $params = []) {
    if (is_array($request)) {
        return pdo_transaction($pdo, $request, $params);
    }

    $request = $pdo->prepare($request);
    $params  = array_values($params);

    if (!$response = $request->execute($params)) {
        $error = $pdo->errorInfo()[2];

        if (!$error) {
            $error = $request->errorInfo()[2];
        }

        if (!$error) {
            $error = 'Failed to query ' . spy($pdo) . ' with ' . spy($request) . '.';
        }

        pfunc_error($error);
    }

    switch (1) {
        case preg_match('/\s*INSERT/i', $request->queryString):
            $response = intval($pdo->lastInsertId());
            break;
        case preg_match('/\s*(?:UPDATE)|(?:DELETE)/i', $request->queryString):
            $response = $request->rowCount();
            break;
        default:
            $response = $request->fetchAll();
    }

    if (!is_array($response)) {
        return $response;
    }

    foreach ($response as $key => $value) {
        $response[$key] = array_key_filter($value, function ($key) {
            return is_string($key);
        });
    }

    return $response;
}

/**
 * rollback a transaction
 *
 * @param resource $pdo is the connection
 * @param string $error is the reason for rollback
 * @return resource
 */
function pdo_rollback($pdo, $error) {
    if (func_num_args() < 2) {
        $error = 'Unkown error.';
    }

    if (!$pdo->rollBack()) {
        pfunc_error('Failed to rollback PDO transaction for ' . spy($pdo) . ' after error "' . $error . '".');
    }

    return $pdo;
}

/**
 * fetch a single row of values from database
 *
 * @param resource $pdo is the connection
 * @param string $request is the sql query
 * @param mixed[] $params is the list of query args
 * @param mixed $default is the value to return in case of empty set
 * @return mixed the row as an assoc array or default value
 */
function pdo_row($pdo, $request, $params = [], $default = null) {
    $response = pdo_query($pdo, $request, $params);

    if (!is_array($response)) {
        return $response;
    }

    if (!$response && func_num_args() > 3) {
        return $default;
    }

    return array_first($response);
}

/**
 * gets or sets the schema being used
 *
 * @param PDO $pdo si teh conncitno
 * @param string $schema is the name of teh schema
 * @return mixed the name of the schema or void
 */
function pdo_schema($pdo, $schema = null) {
    if (func_num_args() < 2) {
        return pdo_cell('SELECT DATABASE()');
    }

    pdo_query($pdo, 'USE ' . $schema);

    return $pdo;
}

/**
 * checks if pdo is in transaction
 *
 * @param PDO $pdo is the pdo
 * @return bool true if yes, false if no
 */
function pdo_transacting($pdo) {
    return $pdo->inTransaction();
}

/**
 * attemps a transaction
 *
 * @param PDO $pdo is the pdo object
 * @param array $requests is the requests
 * @param array $params is the params for each request (defaults ot empty array)
 * @return array and array of the responses for each request
 */
function pdo_transaction($pdo, $requests, $params = []) {
    pdo_begin($pdo);

    try {
        foreach ($requests as $key => $request) {
            $requests[$key] = pdo_query($pdo, $request, $params[$key]);
        }
    } catch (Exception $exception) {
        pdo_rollback($pdo, $exception->getMessage());

        throw $exception;
    }

    pdo_commit($pdo);

    return $requests;
}

/**
 * set the default database
 *
 * @param resource $pdo is the connection
 * @param string $schema is the database name
 * @return resource
 */
function pdo_use(&$pdo, $schema) {
    pdo_query($pdo, 'USE `' . $schema . '`');

    return $pdo;
}