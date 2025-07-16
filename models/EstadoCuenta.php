<?php
class EstadoCuenta {
    private $conn;
    private $celulaMatrizId = 'CE00001'; // hardcodeado para TG

    public function __construct($db) {
        $this->conn = $db;
    }

    private function getWhere($rol, $celulaId) {
        if ($rol === 'TG') {
            return "WHERE IN_CEL_ID = '{$this->celulaMatrizId}'";
        } elseif ($rol === 'TC') {
            return "WHERE IN_CEL_ID = '$celulaId'";
        }
        return ""; // P ve todo
    }

    public function getResumen($rol, $celulaId, $fechaInicio, $fechaFin) {
        $where = $this->getWhere($rol, $celulaId);
        $queryIngresos = "SELECT SUM(IN_MONTO) as total FROM Ingresos $where AND IN_FECHA BETWEEN :inicio AND :fin AND IN_TIPO != 'I'";
        $queryEgresos = "SELECT SUM(EG_MONTO) as total FROM Egresos $where AND EG_FECHA BETWEEN :inicio AND :fin AND EG_ACTIVO = 'A'";

        $stmtIn = $this->conn->prepare($queryIngresos);
        $stmtEg = $this->conn->prepare($queryEgresos);
        $stmtIn->bindParam(':inicio', $fechaInicio);
        $stmtIn->bindParam(':fin', $fechaFin);
        $stmtEg->bindParam(':inicio', $fechaInicio);
        $stmtEg->bindParam(':fin', $fechaFin);
        $stmtIn->execute();
        $stmtEg->execute();

        $ingresos = $stmtIn->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        $egresos = $stmtEg->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        return [
            'total_ingresos' => (float)$ingresos,
            'total_egresos' => (float)$egresos,
            'balance' => (float)$ingresos - (float)$egresos
        ];
    }

    public function getLineaTiempo($rol, $celulaId, $fechaInicio, $fechaFin) {
        $whereIn = $this->getWhere($rol, $celulaId);
        $whereEg = $this->getWhere($rol, $celulaId);

        $query = "
            SELECT 'Ingreso' as tipo, DATE_FORMAT(IN_FECHA, '%Y-%m') as periodo, SUM(IN_MONTO) as total 
            FROM Ingresos $whereIn AND IN_FECHA BETWEEN :inicio AND :fin AND IN_TIPO != 'I'
            GROUP BY periodo
            UNION ALL
            SELECT 'Egreso', DATE_FORMAT(EG_FECHA, '%Y-%m'), SUM(EG_MONTO)
            FROM Egresos $whereEg AND EG_FECHA BETWEEN :inicio AND :fin AND EG_ACTIVO = 'A'
            GROUP BY periodo
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':inicio', $fechaInicio);
        $stmt->bindParam(':fin', $fechaFin);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDetalle($rol, $celulaId, $fechaInicio, $fechaFin) {
        $whereIn = $this->getWhere($rol, $celulaId);
        $whereEg = $this->getWhere($rol, $celulaId);

        $extraCelula = $rol === 'P' ? ", c.CE_NOMBRE as celula_nombre" : "";

        $query = "
            SELECT IN_FECHA as fecha, IN_DESCRIPCION as descripcion, IN_MONTO as monto, 'Ingreso' as tipo $extraCelula
            FROM Ingresos i
            LEFT JOIN Celulas c ON i.IN_CEL_ID = c.CE_ID
            $whereIn AND IN_FECHA BETWEEN :inicio AND :fin AND IN_TIPO != 'I'
            UNION ALL
            SELECT EG_FECHA, EG_DESCRIPCION, EG_MONTO, 'Egreso' $extraCelula
            FROM Egresos e
            LEFT JOIN Celulas c ON e.EG_CEL_ID = c.CE_ID
            $whereEg AND EG_FECHA BETWEEN :inicio AND :fin AND EG_ACTIVO = 'A'
            ORDER BY fecha DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':inicio', $fechaInicio);
        $stmt->bindParam(':fin', $fechaFin);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPastel($rol, $celulaId, $fechaInicio, $fechaFin) {
        $resumen = $this->getResumen($rol, $celulaId, $fechaInicio, $fechaFin);
        return [
            ['label' => 'Ingresos', 'value' => $resumen['total_ingresos']],
            ['label' => 'Egresos', 'value' => $resumen['total_egresos']]
        ];
    }

    public function getBarrasPorCelula($fechaInicio, $fechaFin) {
        $query = "
            SELECT c.CE_NOMBRE as celula, 
                COALESCE(SUM(i.IN_MONTO), 0) as total_ingresos, 
                COALESCE(SUM(e.EG_MONTO), 0) as total_egresos
            FROM Celulas c
            LEFT JOIN Ingresos i ON c.CE_ID = i.IN_CEL_ID AND i.IN_FECHA BETWEEN :inicio AND :fin AND i.IN_TIPO != 'I'
            LEFT JOIN Egresos e ON c.CE_ID = e.EG_CEL_ID AND e.EG_FECHA BETWEEN :inicio AND :fin AND e.EG_ACTIVO = 'A'
            WHERE c.CE_ACTIVO = 'A'
            GROUP BY c.CE_ID
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':inicio', $fechaInicio);
        $stmt->bindParam(':fin', $fechaFin);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
