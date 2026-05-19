<?php
$page_title = 'Proveedores';
require_once 'includes/page_start.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'agregar') {
        $n=trim($_POST['nombre_proveedor']); $c=trim($_POST['contacto']); $t=trim($_POST['telefono']); $e=trim($_POST['email']);
        $stmt=$conexion->prepare("INSERT INTO proveedor(nombre_proveedor,contacto,telefono,email)VALUES(?,?,?,?)");
        $stmt->bind_param("ssss",$n,$c,$t,$e); $stmt->execute()?$msg='success|Proveedor agregado.':$msg='danger|'.$stmt->error; $stmt->close();
    } elseif ($accion === 'editar') {
        $id=$_POST['id']; $n=trim($_POST['nombre_proveedor']); $c=trim($_POST['contacto']); $t=trim($_POST['telefono']); $e=trim($_POST['email']);
        $stmt=$conexion->prepare("UPDATE proveedor SET nombre_proveedor=?,contacto=?,telefono=?,email=? WHERE id_proveedor=?");
        $stmt->bind_param("ssssi",$n,$c,$t,$e,$id); $stmt->execute()?$msg='success|Proveedor actualizado.':$msg='danger|'.$stmt->error; $stmt->close();
    } elseif ($accion === 'eliminar') {
        $id=intval($_POST['id']); $stmt=$conexion->prepare("DELETE FROM proveedor WHERE id_proveedor=?"); $stmt->bind_param("i",$id); $stmt->execute(); $msg='success|Proveedor eliminado.'; $stmt->close();
    }
}
$proveedores=$conexion->query("SELECT * FROM proveedor ORDER BY nombre_proveedor ASC");
[$tipo,$texto]=$msg?explode('|',$msg,2):['',''];
?>
<div class="page-header"><h1 class="page-title">Gestión de <span>Proveedores</span></h1><button class="btn btn-primary" onclick="openModal('modalAgregar')"><i class="fa-solid fa-plus"></i> Nuevo Proveedor</button></div>
<?php if($texto):?><div class="alert alert-<?=$tipo?>"><i class="fa-solid fa-<?=$tipo==='success'?'circle-check':'circle-exclamation'?>"></i><?=htmlspecialchars($texto)?></div><?php endif;?>
<div class="card">
    <div class="card-header"><h2><i class="fa-solid fa-building" style="color:var(--accent2);margin-right:8px;"></i>Lista de Proveedores</h2><div class="search-bar"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Buscar..."></div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>Nombre</th><th>Contacto</th><th>Teléfono</th><th>Email</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php if($proveedores&&$proveedores->num_rows>0):while($p=$proveedores->fetch_assoc()):?>
        <tr><td><?=$p['id_proveedor']?></td><td><strong><?=htmlspecialchars($p['nombre_proveedor'])?></strong></td><td><?=htmlspecialchars($p['contacto']??'—')?></td><td><?=htmlspecialchars($p['telefono']??'—')?></td><td style="color:var(--accent2)"><?=htmlspecialchars($p['email']??'—')?></td>
        <td><div class="td-actions">
            <button class="btn btn-edit btn-sm" onclick="edit(<?=htmlspecialchars(json_encode($p))?>)"><i class="fa-solid fa-pen"></i></button>
            <form method="POST" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?=$p['id_proveedor']?>"><button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-trash"></i></button></form>
        </div></td></tr>
        <?php endwhile;else:?><tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-building"></i><p>No hay proveedores</p></div></td></tr><?php endif;?>
        </tbody>
    </table></div>
</div>
<div class="modal-overlay" id="modalAgregar"><div class="modal"><div class="modal-header"><h3>Nuevo Proveedor</h3><button class="modal-close" onclick="closeModal('modalAgregar')"><i class="fa-solid fa-xmark"></i></button></div>
<form method="POST"><input type="hidden" name="accion" value="agregar"><div class="form-grid form-grid-2">
    <div class="form-group" style="grid-column:1/-1"><label>Nombre *</label><input class="form-control" name="nombre_proveedor" required></div>
    <div class="form-group"><label>Contacto</label><input class="form-control" name="contacto"></div>
    <div class="form-group"><label>Teléfono</label><input class="form-control" name="telefono"></div>
    <div class="form-group" style="grid-column:1/-1"><label>Email</label><input class="form-control" name="email" type="email"></div>
</div><div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;"><button type="button" class="btn" onclick="closeModal('modalAgregar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button></div></form></div></div>
<div class="modal-overlay" id="modalEditar"><div class="modal"><div class="modal-header"><h3>Editar Proveedor</h3><button class="modal-close" onclick="closeModal('modalEditar')"><i class="fa-solid fa-xmark"></i></button></div>
<form method="POST"><input type="hidden" name="accion" value="editar"><input type="hidden" name="id" id="e_id"><div class="form-grid form-grid-2">
    <div class="form-group" style="grid-column:1/-1"><label>Nombre *</label><input class="form-control" name="nombre_proveedor" id="e_n" required></div>
    <div class="form-group"><label>Contacto</label><input class="form-control" name="contacto" id="e_c"></div>
    <div class="form-group"><label>Teléfono</label><input class="form-control" name="telefono" id="e_t"></div>
    <div class="form-group" style="grid-column:1/-1"><label>Email</label><input class="form-control" name="email" id="e_e" type="email"></div>
</div><div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;"><button type="button" class="btn" onclick="closeModal('modalEditar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Actualizar</button></div></form></div></div>
<script>function edit(p){document.getElementById('e_id').value=p.id_proveedor;document.getElementById('e_n').value=p.nombre_proveedor;document.getElementById('e_c').value=p.contacto??'';document.getElementById('e_t').value=p.telefono??'';document.getElementById('e_e').value=p.email??'';openModal('modalEditar');}</script>
<?php require_once 'includes/page_end.php'; ?>
