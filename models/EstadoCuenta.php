<?php
class EstadoCuenta {
    private $conn;
    private $celulaMatrizId = 'CE00001'; // hardcodeado para TG

    public function __construct($db) {
        $this->conn = $db;
    }

   private function getWhere($rol, $celulaId, $tabla = 'IN') {
        $campoCelula = ($tabla === 'IN') ? 'IN_CEL_ID' : 'EG_CEL_ID';

        if ($rol === 'TG') {
            return "$campoCelula = '{$this->celulaMatrizId}'";
        } elseif ($rol === 'TC') {
            return "$campoCelula = '$celulaId'";
        }
        return ""; // P ve todo
    }

    public function getResumen($rol, $celulaId, $fechaInicio, $fechaFin) {
        $condIn = $this->getWhere($rol, $celulaId, 'IN');
        $condEg = $this->getWhere($rol, $celulaId, 'EG');

        $whereIn = $condIn ? "WHERE $condIn AND IN_FECHA BETWEEN :inicio AND :fin AND IN_TIPO != 'I'"
                        : "WHERE IN_FECHA BETWEEN :inicio AND :fin AND IN_TIPO != 'I'";
        $whereEg = $condEg ? "WHERE $condEg AND EG_FECHA BETWEEN :inicio AND :fin AND EG_ACTIVO = 'A'"
                        : "WHERE EG_FECHA BETWEEN :inicio AND :fin AND EG_ACTIVO = 'A'";

        $queryIngresos = "SELECT SUM(IN_MONTO) as total FROM Ingresos $whereIn";
        $queryEgresos = "SELECT SUM(EG_MONTO) as total FROM Egresos $whereEg";

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
        $condIn = $this->getWhere($rol, $celulaId, 'IN');
        $condEg = $this->getWhere($rol, $celulaId, 'EG');

        $whereIn = $condIn ? "WHERE $condIn AND IN_FECHA BETWEEN :inicio AND :fin AND IN_TIPO != 'I'"
                        : "WHERE IN_FECHA BETWEEN :inicio AND :fin AND IN_TIPO != 'I'";
        $whereEg = $condEg ? "WHERE $condEg AND EG_FECHA BETWEEN :inicio AND :fin AND EG_ACTIVO = 'A'"
                        : "WHERE EG_FECHA BETWEEN :inicio AND :fin AND EG_ACTIVO = 'A'";

        $query = "
            SELECT 'Ingreso' as tipo, TO_CHAR(IN_FECHA, 'YYYY-MM') as periodo, SUM(IN_MONTO) as total 
            FROM Ingresos $whereIn
            GROUP BY TO_CHAR(IN_FECHA, 'YYYY-MM')
            UNION ALL
            SELECT 'Egreso', TO_CHAR(EG_FECHA, 'YYYY-MM') as periodo, SUM(EG_MONTO)
            FROM Egresos $whereEg
            GROUP BY TO_CHAR(EG_FECHA, 'YYYY-MM')
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':inicio', $fechaInicio);
        $stmt->bindParam(':fin', $fechaFin);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDetalle($rol, $celulaId, $fechaInicio, $fechaFin) {
        $condIn = $this->getWhere($rol, $celulaId, 'IN');
        $condEg = $this->getWhere($rol, $celulaId, 'EG');

        $extraCelula = $rol === 'P' ? ", c.CE_NOMBRE as celula_nombre" : "";

        $whereIn = $condIn ? "WHERE $condIn AND IN_FECHA BETWEEN :inicio AND :fin AND IN_TIPO != 'I'"
                        : "WHERE IN_FECHA BETWEEN :inicio AND :fin AND IN_TIPO != 'I'";
        $whereEg = $condEg ? "WHERE $condEg AND EG_FECHA BETWEEN :inicio AND :fin AND EG_ACTIVO = 'A'"
                        : "WHERE EG_FECHA BETWEEN :inicio AND :fin AND EG_ACTIVO = 'A'";

        $query = "
            SELECT IN_FECHA as fecha, IN_DESCRIPCION as descripcion, IN_MONTO as monto, 'Ingreso' as tipo $extraCelula
            FROM Ingresos i
            LEFT JOIN Celulas c ON i.IN_CEL_ID = c.CE_ID
            $whereIn
            UNION ALL
            SELECT EG_FECHA, EG_DESCRIPCION, EG_MONTO, 'Egreso' $extraCelula
            FROM Egresos e
            LEFT JOIN Celulas c ON e.EG_CEL_ID = c.CE_ID
            $whereEg
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
