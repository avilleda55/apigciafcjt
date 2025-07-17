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
    $condIn = ["IN_TIPO != 'I'"];
    $condEg = ["EG_ACTIVO = 'A'"];

    if ($rol === 'TC') {
        $condIn[] = "IN_CEL_ID = :celula";
        $condEg[] = "EG_CEL_ID = :celula";
    }

    $whereIn = 'WHERE ' . implode(' AND ', $condIn);
    $whereEg = 'WHERE ' . implode(' AND ', $condEg);

    $query = "
        SELECT IN_ID as id, IN_DESCRIPCION as descripcion, IN_MONTO as monto, IN_FECHA as fecha, 'Ingreso' as tipo 
        FROM Ingresos $whereIn
        UNION ALL
        SELECT EG_ID, EG_DESCRIPCION, EG_MONTO, EG_FECHA, 'Egreso' 
        FROM Egresos $whereEg
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


}
