<?php
$page_title = 'Categorías';
require_once 'includes/page_start.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'agregar') {
        $nombre = trim($_POST['nombre_categoria']);
        $desc   = trim($_POST['descripcion']);
        $stmt = $conexion->prepare("INSERT INTO categoria_producto (nombre_categoria,descripcion) VALUES(?,?)");
        $stmt->bind_param("ss",$nombre,$desc);
        $stmt->execute() ? $msg='success|Categoría agregada.' : $msg='danger|'.$stmt->error;
        $stmt->close();
    } elseif ($accion === 'editar') {
        $id=$_POST['id']; $nombre=trim($_POST['nombre_categoria']); $desc=trim($_POST['descripcion']);
        $stmt = $conexion->prepare("UPDATE categoria_producto SET nombre_categoria=?,descripcion=? WHERE id_categoria=?");
        $stmt->bind_param("ssi",$nombre,$desc,$id);
        $stmt->execute() ? $msg='success|Categoría actualizada.' : $msg='danger|'.$stmt->error;
        $stmt->close();
    } elseif ($accion === 'eliminar') {
        $id=intval($_POST['id']);
        $stmt=$conexion->prepare("DELETE FROM categoria_producto WHERE id_categoria=?");
        $stmt->bind_param("i",$id); $stmt->execute();
        $msg='success|Categoría eliminada.'; $stmt->close();
    }
}
$cats = $conexion->query("SELECT * FROM categoria_producto ORDER BY nombre_categoria ASC");
[$tipo,$texto] = $msg ? explode('|',$msg,2) : ['',''];
?>
<div class="page-header">
    <h1 class="page-title">Gestión de <span>Categorías</span></h1>
    <button class="btn btn-primary" onclick="openModal('modalAgregar')"><i class="fa-solid fa-plus"></i> Nueva Categoría</button>
</div>
<?php if ($texto): ?><div class="alert alert-<?=$tipo?>"><i class="fa-solid fa-<?=$tipo==='success'?'circle-check':'circle-exclamation'?>"></i><?=htmlspecialchars($texto)?></div><?php endif; ?>
<div class="card">
    <div class="card-header">
        <h2><i class="fa-solid fa-tags" style="color:var(--accent2);margin-right:8px;"></i>Categorías de Producto</h2>
        <div class="search-bar"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Buscar..."></div>
    </div>
    <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>Nombre</th><th>Descripción</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php if($cats&&$cats->num_rows>0): while($c=$cats->fetch_assoc()): ?>
        <tr>
            <td><?=$c['id_categoria']?></td>
            <td><strong><?=htmlspecialchars($c['nombre_categoria'])?></strong></td>
            <td style="color:var(--muted)"><?=htmlspecialchars($c['descripcion']??'—')?></td>
            <td><div class="td-actions">
                <button class="btn btn-edit btn-sm" onclick="editarCat(<?=htmlspecialchars(json_encode($c))?>)"><i class="fa-solid fa-pen"></i></button>
                <form method="POST" onsubmit="return confirm('¿Eliminar?')">
                    <input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?=$c['id_categoria']?>">
                    <button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-trash"></i></button>
                </form>
            </div></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="4"><div class="empty-state"><i class="fa-solid fa-tags"></i><p>No hay categorías</p></div></td></tr>
        <?php endif; ?>
        </tbody>
    </table></div>
</div>
<!-- MODAL AGREGAR -->
<div class="modal-overlay" id="modalAgregar"><div class="modal">
    <div class="modal-header"><h3><i class="fa-solid fa-plus" style="color:var(--accent2);margin-right:8px;"></i>Nueva Categoría</h3><button class="modal-close" onclick="closeModal('modalAgregar')"><i class="fa-solid fa-xmark"></i></button></div>
    <form method="POST"><input type="hidden" name="accion" value="agregar">
        <div class="form-grid">
            <div class="form-group"><label>Nombre *</label><input class="form-control" name="nombre_categoria" required></div>
            <div class="form-group"><label>Descripción</label><textarea class="form-control" name="descripcion" rows="2"></textarea></div>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
            <button type="button" class="btn" onclick="closeModal('modalAgregar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
        </div>
    </form>
</div></div>
<!-- MODAL EDITAR -->
<div class="modal-overlay" id="modalEditar"><div class="modal">
    <div class="modal-header"><h3><i class="fa-solid fa-pen" style="color:var(--accent2);margin-right:8px;"></i>Editar Categoría</h3><button class="modal-close" onclick="closeModal('modalEditar')"><i class="fa-solid fa-xmark"></i></button></div>
    <form method="POST"><input type="hidden" name="accion" value="editar"><input type="hidden" name="id" id="edit_id">
        <div class="form-grid">
            <div class="form-group"><label>Nombre *</label><input class="form-control" name="nombre_categoria" id="edit_nombre" required></div>
            <div class="form-group"><label>Descripción</label><textarea class="form-control" name="descripcion" id="edit_desc" rows="2"></textarea></div>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
            <button type="button" class="btn" onclick="closeModal('modalEditar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Actualizar</button>
        </div>
    </form>
</div></div>
<script>
function editarCat(c){document.getElementById('edit_id').value=c.id_categoria;document.getElementById('edit_nombre').value=c.nombre_categoria;document.getElementById('edit_desc').value=c.descripcion??'';openModal('modalEditar');}
</script>
<?php require_once 'includes/page_end.php'; ?>
