import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;

/**
 * EstadisticasBilling — Villalobos Logística
 * Conecta a MySQL vía JDBC y calcula el cierre mensual.
 * Salida: JSON por stdout.
 *
 * Compilar: javac -cp libs/mysql-connector-j.jar EstadisticasBilling.java
 *           jar cfe EstadisticasBilling.jar EstadisticasBilling *.class
 */
public class EstadisticasBilling {

    private static final String DB_URL  =
        "jdbc:mysql://localhost:3306/villalobos_logistica_2" +
        "?useSSL=false&allowPublicKeyRetrieval=true&characterEncoding=UTF-8";
    private static final String DB_USER = "root";
    private static final String DB_PASS = "";

    public static void main(String[] args) {

        String mes = (args.length > 0) ? args[0]
                   : LocalDate.now().format(DateTimeFormatter.ofPattern("yyyy-MM"));

        try (Connection conn = DriverManager.getConnection(DB_URL, DB_USER, DB_PASS)) {

            int totalPortes = queryInt(conn,
                "SELECT COUNT(*) FROM portes WHERE DATE_FORMAT(fecha_programada,'%Y-%m') = ?", mes);

            double ingresos = queryDouble(conn,
                "SELECT COALESCE(SUM(precio),0) FROM portes " +
                "WHERE estado='entregado' AND DATE_FORMAT(fecha_programada,'%Y-%m') = ?", mes);

            double kmTotales = queryDouble(conn,
                "SELECT COALESCE(SUM(kms),0) FROM portes " +
                "WHERE DATE_FORMAT(fecha_programada,'%Y-%m') = ?", mes);

            double mediaIngreso = queryDouble(conn,
                "SELECT COALESCE(AVG(precio),0) FROM portes " +
                "WHERE estado='entregado' AND DATE_FORMAT(fecha_programada,'%Y-%m') = ?", mes);

            String conductorTop = "Sin datos";
            int    portesTop    = 0;
            try (PreparedStatement ps = conn.prepareStatement(
                    "SELECT u.nombre, COUNT(*) AS total FROM portes p " +
                    "JOIN usuarios u ON p.conductor_id = u.id " +
                    "WHERE DATE_FORMAT(p.fecha_programada,'%Y-%m') = ? " +
                    "GROUP BY p.conductor_id ORDER BY total DESC LIMIT 1")) {
                ps.setString(1, mes);
                ResultSet rs = ps.executeQuery();
                if (rs.next()) {
                    conductorTop = rs.getString("nombre");
                    portesTop    = rs.getInt("total");
                }
            }

            StringBuilder porEstado = new StringBuilder();
            try (PreparedStatement ps = conn.prepareStatement(
                    "SELECT estado, COUNT(*) AS total FROM portes " +
                    "WHERE DATE_FORMAT(fecha_programada,'%Y-%m') = ? GROUP BY estado")) {
                ps.setString(1, mes);
                ResultSet rs = ps.executeQuery();
                boolean primero = true;
                while (rs.next()) {
                    if (!primero) porEstado.append(",");
                    porEstado.append("\"").append(rs.getString("estado"))
                             .append("\":").append(rs.getInt("total"));
                    primero = false;
                }
            }

            System.out.println("{");
            System.out.println("  \"ok\": true,");
            System.out.println("  \"mes\": \""         + mes          + "\",");
            System.out.println("  \"total_portes\": "  + totalPortes  + ",");
            System.out.printf ("  \"ingresos_mes\": %.2f,%n",  ingresos);
            System.out.printf ("  \"km_totales\": %.1f,%n",    kmTotales);
            System.out.printf ("  \"media_porte\": %.2f,%n",   mediaIngreso);
            System.out.println("  \"conductor_top\": \"" + conductorTop + "\",");
            System.out.println("  \"portes_conductor\": " + portesTop  + ",");
            System.out.println("  \"por_estado\": {" + porEstado + "}");
            System.out.println("}");

        } catch (SQLException e) {
            String msg = e.getMessage().replace("\"", "'").replace("\n", " ");
            System.out.println("{\"ok\":false,\"error\":\"" + msg + "\"}");
            System.exit(1);
        }
    }

    private static int queryInt(Connection conn, String sql, String param) throws SQLException {
        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setString(1, param);
            ResultSet rs = ps.executeQuery();
            return rs.next() ? rs.getInt(1) : 0;
        }
    }

    private static double queryDouble(Connection conn, String sql, String param) throws SQLException {
        try (PreparedStatement ps = conn.prepareStatement(sql)) {
            ps.setString(1, param);
            ResultSet rs = ps.executeQuery();
            return rs.next() ? rs.getDouble(1) : 0.0;
        }
    }
}
