<?php
class Dashboard {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getSummary($rol, $celulaId = null) {
    $conditionsIn = ["IN_TIPO != 'I'"];
    $conditionsEg = ["EG_ACTIVO = 'A'"];

    if ($rol === 'TC') {
        $conditionsIn[] = "IN_CEL_ID = :celula";
        $conditionsEg[] = "EG_CEL_ID = :celula";
    }

    $whereIn = 'WHERE ' . implode(' AND ', $conditionsIn);
    $whereEg = 'WHERE ' . implode(' AND ', $conditionsEg);

    $queryIngresos = "SELECT SUM(IN_MONTO) as total FROM Ingresos $whereIn";
    $queryEgresos = "SELECT SUM(EG_MONTO) as total FROM Egresos $whereEg";

    $stmtIn = $this->conn->prepare($queryIngresos);
    $stmtEg = $this->conn->prepare($queryEgresos);

    if ($rol === 'TC') {
        $stmtIn->bindParam(':celula', $celulaId);
        $stmtEg->bindParam(':celula', $celulaId);
    }

    $stmtIn->execute();
    $stmtEg->execute();

    $ingresos = $stmtIn->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $egresos = $stmtEg->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    return [
        'ingresos' => $ingresos,
        'egresos' => $egresos,
        'balance' => $ingresos - $egresos
    ];
}


public function getCombinedList($rol, $celulaId = null) {
    $condIn = ["i.IN_TIPO != 'I'"];
    $condEg = ["e.EG_ACTIVO = 'A'"];

    if ($rol === 'TC') {
        $condIn[] = "i.IN_CEL_ID = :celula";
        $condEg[] = "e.EG_CEL_ID = :celula";
    }

    $whereIn = 'WHERE ' . implode(' AND ', $condIn);
    $whereEg = 'WHERE ' . implode(' AND ', $condEg);

    $query = "
        SELECT i.IN_ID as id, i.IN_DESCRIPCION as descripcion, i.IN_MONTO as monto, i.IN_FECHA as fecha, 'Ingreso' as tipo, c.CE_NOMBRE as celula
        FROM Ingresos i
        JOIN Celulas c ON c.CE_ID = i.IN_CEL_ID
        $whereIn
        UNION ALL
        SELECT e.EG_ID, e.EG_DESCRIPCION, e.EG_MONTO, e.EG_FECHA, 'Egreso', c.CE_NOMBRE
        FROM Egresos e
        JOIN Celulas c ON c.CE_ID = e.EG_CEL_ID
        $whereEg
        ORDER BY fecha DESC
    ";

    $stmt = $this->conn->prepare($query);
    if ($rol === 'TC') {
        $stmt->bindParam(':celula', $celulaId);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



  public function getMonthlyEvolution($rol, $celulaId = null) {
    $condIn = ["IN_TIPO != 'I'"];
    $condEg = ["EG_ACTIVO = 'A'"];

    if ($rol === 'TC') {
        $condIn[] = "IN_CEL_ID = :celula";
        $condEg[] = "EG_CEL_ID = :celula";
    }

    $whereIn = 'WHERE ' . implode(' AND ', $condIn);
    $whereEg = 'WHERE ' . implode(' AND ', $condEg);

    $query = "
        SELECT 'Ingreso' as tipo, TO_CHAR(IN_FECHA, 'YYYY-MM') as periodo, SUM(IN_MONTO) as total 
        FROM Ingresos $whereIn 
        GROUP BY TO_CHAR(IN_FECHA, 'YYYY-MM')
        UNION ALL
        SELECT 'Egreso', TO_CHAR(EG_FECHA, 'YYYY-MM') as periodo, SUM(EG_MONTO)
        FROM Egresos $whereEg 
        GROUP BY TO_CHAR(EG_FECHA, 'YYYY-MM')
        ORDER BY periodo
    ";

    $stmt = $this->conn->prepare($query);
    if ($rol === 'TC') {
        $stmt->bindParam(':celula', $celulaId);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function getCombinedList($rol, $celulaId = null) {
    $condIn = ["i.IN_TIPO != 'I'"];
    $condEg = ["e.EG_ACTIVO = 'A'"];

    if ($rol === 'TC') {
        $condIn[] = "i.IN_CEL_ID = :celula";
        $condEg[] = "e.EG_CEL_ID = :celula";
    }

    $whereIn = 'WHERE ' . implode(' AND ', $condIn);
    $whereEg = 'WHERE ' . implode(' AND ', $condEg);

    $query = "
        SELECT i.IN_ID as id, i.IN_DESCRIPCION as descripcion, i.IN_MONTO as monto, i.IN_FECHA as fecha, 'Ingreso' as tipo, c.CE_NOMBRE as celula
        FROM Ingresos i
        JOIN Celulas c ON c.CE_ID = i.IN_CEL_ID
        $whereIn
        UNION ALL
        SELECT e.EG_ID, e.EG_DESCRIPCION, e.EG_MONTO, e.EG_FECHA, 'Egreso', c.CE_NOMBRE
        FROM Egresos e
        JOIN Celulas c ON c.CE_ID = e.EG_CEL_ID
        $whereEg
        ORDER BY fecha DESC
    ";

    $stmt = $this->conn->prepare($query);
    if ($rol === 'TC') {
        $stmt->bindParam(':celula', $celulaId);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


}
