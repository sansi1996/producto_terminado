<?php
$page_title = 'Productos';
require_once 'includes/page_start.php';

$msg = '';

// ── ACCIONES POST ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'agregar') {
        $nombre   = trim($_POST['nombre']);
        $desc     = trim($_POST['descripcion']);
        $precio   = floatval($_POST['precio_unitario']);
        $stock    = intval($_POST['stock_actual']);
        $fecha    = $_POST['fecha_registro'];
        $estado   = $_POST['estado'];
        $stmt = $conexion->prepare("INSERT INTO producto_terminado (nombre,descripcion,precio_unitario,stock_actual,fecha_registro,estado) VALUES(?,?,?,?,?,?)");
        $stmt->bind_param("ssdiss", $nombre,$desc,$precio,$stock,$fecha,$estado);
        $stmt->execute() ? $msg = 'success|Producto agregado correctamente.' : $msg = 'danger|'.$stmt->error;
        $stmt->close();

    } elseif ($accion === 'editar') {
        $id=$_POST['id']; $nombre=trim($_POST['nombre']); $desc=trim($_POST['descripcion']);
        $precio=floatval($_POST['precio_unitario']); $stock=intval($_POST['stock_actual']);
        $fecha=$_POST['fecha_registro']; $estado=$_POST['estado'];
        $stmt = $conexion->prepare("UPDATE producto_terminado SET nombre=?,descripcion=?,precio_unitario=?,stock_actual=?,fecha_registro=?,estado=? WHERE id_producto=?");
        $stmt->bind_param("ssdissi",$nombre,$desc,$precio,$stock,$fecha,$estado,$id);
        $stmt->execute() ? $msg='success|Producto actualizado.' : $msg='danger|'.$stmt->error;
        $stmt->close();

    } elseif ($accion === 'eliminar') {
        $id = intval($_POST['id']);
        $stmt = $conexion->prepare("DELETE FROM producto_terminado WHERE id_producto=?");
        $stmt->bind_param("i",$id); $stmt->execute();
        $stmt->execute() ? $msg='success|Producto eliminado.' : $msg='danger|'.$stmt->error;
        $stmt->close();
    }
}

// ── LEER DATOS ────────────────────────────────────────────
$productos = $conexion->query("SELECT * FROM producto_terminado ORDER BY nombre ASC");

// ── MENSAJE ──────────────────────────────────────────────
[$tipo,$texto] = $msg ? explode('|',$msg,2) : ['',''];
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <h1 class="page-title">Gestión de <span>Productos</span></h1>
    <button class="btn btn-primary" onclick="openModal('modalAgregar')">
        <i class="fa-solid fa-plus"></i> Nuevo Producto
    </button>
</div>

<?php if ($texto): ?>
<div class="alert alert-<?= $tipo ?>"><i class="fa-solid fa-<?= $tipo==='success'?'circle-check':'circle-exclamation' ?>"></i><?= htmlspecialchars($texto) ?></div>
<?php endif; ?>

<!-- TABLA -->
<div class="card">
    <div class="card-header">
        <h2><i class="fa-solid fa-boxes-stacked" style="color:var(--accent2);margin-right:8px;"></i>Inventario de Productos</h2>
        <div class="search-bar"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Buscar..."></div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>#</th><th>Nombre</th><th>Descripción</th><th>Precio Unit.</th><th>Stock</th><th>Fecha Reg.</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php if ($productos && $productos->num_rows > 0): while($p = $productos->fetch_assoc()): ?>
            <tr>
                <td><?= $p['id_producto'] ?></td>
                <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                <td style="color:var(--muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($p['descripcion']??'—') ?></td>
                <td>$<?= number_format($p['precio_unitario'],2) ?></td>
                <td><span class="badge <?= $p['stock_actual']<=5?'badge-orange':'badge-blue' ?>"><?= $p['stock_actual'] ?></span></td>
                <td><?= $p['fecha_registro'] ?></td>
                <td><span class="badge <?= $p['estado']==='ACTIVO'?'badge-active':'badge-inactive' ?>"><?= $p['estado'] ?></span></td>
                <td><div class="td-actions">
                    <button class="btn btn-edit btn-sm" onclick="editarProducto(<?= htmlspecialchars(json_encode($p)) ?>)"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" onsubmit="return confirm('¿Eliminar este producto?')">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?= $p['id_producto'] ?>">
                        <button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </div></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="8"><div class="empty-state"><i class="fa-solid fa-boxes-stacked"></i><p>No hay productos registrados</p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL AGREGAR -->
<div class="modal-overlay" id="modalAgregar">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fa-solid fa-plus" style="color:var(--accent2);margin-right:8px;"></i>Nuevo Producto</h3>
            <button class="modal-close" onclick="closeModal('modalAgregar')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="accion" value="agregar">
            <div class="form-grid form-grid-2">
                <div class="form-group" style="grid-column:1/-1"><label>Nombre *</label><input class="form-control" name="nombre" required></div>
                <div class="form-group" style="grid-column:1/-1"><label>Descripción</label><textarea class="form-control" name="descripcion" rows="2"></textarea></div>
                <div class="form-group"><label>Precio Unitario *</label><input class="form-control" name="precio_unitario" type="number" step="0.01" min="0" required></div>
                <div class="form-group"><label>Stock Actual *</label><input class="form-control" name="stock_actual" type="number" min="0" required></div>
                <div class="form-group"><label>Fecha Registro *</label><input class="form-control" name="fecha_registro" type="date" value="<?= date('Y-m-d') ?>" required></div>
                <div class="form-group"><label>Estado</label>
                    <select class="form-control" name="estado">
                        <option value="ACTIVO">ACTIVO</option><option value="INACTIVO">INACTIVO</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:22px;">
                <button type="button" class="btn" onclick="closeModal('modalAgregar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDITAR -->
<div class="modal-overlay" id="modalEditar">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fa-solid fa-pen" style="color:var(--accent2);margin-right:8px;"></i>Editar Producto</h3>
            <button class="modal-close" onclick="closeModal('modalEditar')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-grid form-grid-2">
                <div class="form-group" style="grid-column:1/-1"><label>Nombre *</label><input class="form-control" name="nombre" id="edit_nombre" required></div>
                <div class="form-group" style="grid-column:1/-1"><label>Descripción</label><textarea class="form-control" name="descripcion" id="edit_descripcion" rows="2"></textarea></div>
                <div class="form-group"><label>Precio Unitario *</label><input class="form-control" name="precio_unitario" id="edit_precio" type="number" step="0.01" min="0" required></div>
                <div class="form-group"><label>Stock Actual *</label><input class="form-control" name="stock_actual" id="edit_stock" type="number" min="0" required></div>
                <div class="form-group"><label>Fecha Registro *</label><input class="form-control" name="fecha_registro" id="edit_fecha" type="date" required></div>
                <div class="form-group"><label>Estado</label>
                    <select class="form-control" name="estado" id="edit_estado">
                        <option value="ACTIVO">ACTIVO</option><option value="INACTIVO">INACTIVO</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:22px;">
                <button type="button" class="btn" onclick="closeModal('modalEditar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Actualizar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarProducto(p) {
    document.getElementById('edit_id').value       = p.id_producto;
    document.getElementById('edit_nombre').value   = p.nombre;
    document.getElementById('edit_descripcion').value = p.descripcion ?? '';
    document.getElementById('edit_precio').value   = p.precio_unitario;
    document.getElementById('edit_stock').value    = p.stock_actual;
    document.getElementById('edit_fecha').value    = p.fecha_registro;
    document.getElementById('edit_estado').value   = p.estado;
    openModal('modalEditar');
}
</script>

<?php require_once 'includes/page_end.php'; ?>
