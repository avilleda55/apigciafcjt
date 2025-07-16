<?php
class Dashboard {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getSummary($rol, $celulaId = null) {
        $where = ($rol === 'TC') ? "WHERE IN_CEL_ID = :celula" : "";
        $queryIngresos = "SELECT SUM(IN_MONTO) as total FROM Ingresos $where AND IN_TIPO != 'I'";
        $queryEgresos = "SELECT SUM(EG_MONTO) as total FROM Egresos $where AND EG_ACTIVO = 'A'";

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
        $where = ($rol === 'TC') ? "WHERE IN_CEL_ID = :celula" : "";

        $query = "
            SELECT IN_ID as id, IN_DESCRIPCION as descripcion, IN_MONTO as monto, IN_FECHA as fecha, 'Ingreso' as tipo 
            FROM Ingresos 
            WHERE IN_TIPO != 'I' " . ($rol === 'TC' ? "AND IN_CEL_ID = :celula" : "") . "
            UNION ALL
            SELECT EG_ID, EG_DESCRIPCION, EG_MONTO, EG_FECHA, 'Egreso' 
            FROM Egresos 
            WHERE EG_ACTIVO = 'A' " . ($rol === 'TC' ? "AND EG_CEL_ID = :celula" : "") . "
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
        $whereIn = ($rol === 'TC') ? "WHERE IN_CEL_ID = :celula AND IN_TIPO != 'I'" : "WHERE IN_TIPO != 'I'";
        $whereEg = ($rol === 'TC') ? "WHERE EG_CEL_ID = :celula AND EG_ACTIVO = 'A'" : "WHERE EG_ACTIVO = 'A'";

        $query = "
            SELECT 'Ingreso' as tipo, DATE_FORMAT(IN_FECHA, '%Y-%m') as periodo, SUM(IN_MONTO) as total 
            FROM Ingresos $whereIn GROUP BY periodo
            UNION ALL
            SELECT 'Egreso', DATE_FORMAT(EG_FECHA, '%Y-%m'), SUM(EG_MONTO)
            FROM Egresos $whereEg GROUP BY periodo
        ";

        $stmt = $this->conn->prepare($query);
        if ($rol === 'TC') {
            $stmt->bindParam(':celula', $celulaId);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
