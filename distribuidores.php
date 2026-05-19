<?php
$page_title = 'Distribuidores';
require_once 'includes/page_start.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'agregar') {
        $n=trim($_POST['nombre_empresa']); $t=trim($_POST['telefono']); $e=trim($_POST['email']); $d=trim($_POST['direccion']); $c=trim($_POST['ciudad']); $est=trim($_POST['estado']);
        $stmt=$conexion->prepare("INSERT INTO distribuidor(nombre_empresa,telefono,email,direccion,ciudad,estado)VALUES(?,?,?,?,?,?)");
        $stmt->bind_param("ssssss",$n,$t,$e,$d,$c,$est); $stmt->execute()?$msg='success|Distribuidor agregado.':$msg='danger|'.$stmt->error; $stmt->close();
    } elseif ($accion === 'editar') {
        $id=$_POST['id']; $n=trim($_POST['nombre_empresa']); $t=trim($_POST['telefono']); $e=trim($_POST['email']); $d=trim($_POST['direccion']); $c=trim($_POST['ciudad']); $est=trim($_POST['estado']);
        $stmt=$conexion->prepare("UPDATE distribuidor SET nombre_empresa=?,telefono=?,email=?,direccion=?,ciudad=?,estado=? WHERE id_distribuidor=?");
        $stmt->bind_param("ssssssi",$n,$t,$e,$d,$c,$est,$id); $stmt->execute()?$msg='success|Distribuidor actualizado.':$msg='danger|'.$stmt->error; $stmt->close();
    } elseif ($accion === 'eliminar') {
        $id=intval($_POST['id']); $stmt=$conexion->prepare("DELETE FROM distribuidor WHERE id_distribuidor=?"); $stmt->bind_param("i",$id); $stmt->execute(); $msg='success|Distribuidor eliminado.'; $stmt->close();
    }
}
$distribuidores=$conexion->query("SELECT * FROM distribuidor ORDER BY nombre_empresa ASC");
[$tipo,$texto]=$msg?explode('|',$msg,2):['',''];
?>
<div class="page-header"><h1 class="page-title">Gestión de <span>Distribuidores</span></h1><button class="btn btn-primary" onclick="openModal('modalAgregar')"><i class="fa-solid fa-plus"></i> Nuevo Distribuidor</button></div>
<?php if($texto):?><div class="alert alert-<?=$tipo?>"><i class="fa-solid fa-<?=$tipo==='success'?'circle-check':'circle-exclamation'?>"></i><?=htmlspecialchars($texto)?></div><?php endif;?>
<div class="card">
    <div class="card-header"><h2><i class="fa-solid fa-truck" style="color:var(--accent2);margin-right:8px;"></i>Red de Distribuidores</h2><div class="search-bar"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Buscar..."></div></div>
    <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>Empresa</th><th>Ciudad</th><th>Teléfono</th><th>Email</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php if($distribuidores&&$distribuidores->num_rows>0):while($d=$distribuidores->fetch_assoc()):?>
        <tr><td><?=$d['id_distribuidor']?></td><td><strong><?=htmlspecialchars($d['nombre_empresa'])?></strong></td><td><?=htmlspecialchars($d['ciudad']??'—')?></td><td><?=htmlspecialchars($d['telefono']??'—')?></td><td style="color:var(--accent2)"><?=htmlspecialchars($d['email']??'—')?></td>
        <td><span class="badge <?=$d['estado']==='ACTIVO'?'badge-active':'badge-inactive'?>"><?=$d['estado']?></span></td>
        <td><div class="td-actions">
            <button class="btn btn-edit btn-sm" onclick="edit(<?=htmlspecialchars(json_encode($d))?>)"><i class="fa-solid fa-pen"></i></button>
            <form method="POST" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?=$d['id_distribuidor']?>"><button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-trash"></i></button></form>
        </div></td></tr>
        <?php endwhile;else:?><tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-truck"></i><p>No hay distribuidores</p></div></td></tr><?php endif;?>
        </tbody>
    </table></div>
</div>
<div class="modal-overlay" id="modalAgregar"><div class="modal"><div class="modal-header"><h3>Nuevo Distribuidor</h3><button class="modal-close" onclick="closeModal('modalAgregar')"><i class="fa-solid fa-xmark"></i></button></div>
<form method="POST"><input type="hidden" name="accion" value="agregar"><div class="form-grid form-grid-2">
    <div class="form-group" style="grid-column:1/-1"><label>Nombre Empresa *</label><input class="form-control" name="nombre_empresa" required></div>
    <div class="form-group"><label>Teléfono</label><input class="form-control" name="telefono"></div>
    <div class="form-group"><label>Email</label><input class="form-control" name="email" type="email"></div>
    <div class="form-group"><label>Ciudad</label><input class="form-control" name="ciudad"></div>
    <div class="form-group"><label>Estado</label><select class="form-control" name="estado"><option value="ACTIVO">ACTIVO</option><option value="INACTIVO">INACTIVO</option></select></div>
    <div class="form-group" style="grid-column:1/-1"><label>Dirección</label><input class="form-control" name="direccion"></div>
</div><div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;"><button type="button" class="btn" onclick="closeModal('modalAgregar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button></div></form></div></div>
<div class="modal-overlay" id="modalEditar"><div class="modal"><div class="modal-header"><h3>Editar Distribuidor</h3><button class="modal-close" onclick="closeModal('modalEditar')"><i class="fa-solid fa-xmark"></i></button></div>
<form method="POST"><input type="hidden" name="accion" value="editar"><input type="hidden" name="id" id="e_id"><div class="form-grid form-grid-2">
    <div class="form-group" style="grid-column:1/-1"><label>Nombre Empresa *</label><input class="form-control" name="nombre_empresa" id="e_n" required></div>
    <div class="form-group"><label>Teléfono</label><input class="form-control" name="telefono" id="e_t"></div>
    <div class="form-group"><label>Email</label><input class="form-control" name="email" id="e_e" type="email"></div>
    <div class="form-group"><label>Ciudad</label><input class="form-control" name="ciudad" id="e_c"></div>
    <div class="form-group"><label>Estado</label><select class="form-control" name="estado" id="e_est"><option value="ACTIVO">ACTIVO</option><option value="INACTIVO">INACTIVO</option></select></div>
    <div class="form-group" style="grid-column:1/-1"><label>Dirección</label><input class="form-control" name="direccion" id="e_d"></div>
</div><div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;"><button type="button" class="btn" onclick="closeModal('modalEditar')" style="background:var(--surface2);color:var(--muted);">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Actualizar</button></div></form></div></div>
<script>function edit(d){document.getElementById('e_id').value=d.id_distribuidor;document.getElementById('e_n').value=d.nombre_empresa;document.getElementById('e_t').value=d.telefono??'';document.getElementById('e_e').value=d.email??'';document.getElementById('e_c').value=d.ciudad??'';document.getElementById('e_est').value=d.estado;document.getElementById('e_d').value=d.direccion??'';openModal('modalEditar');}</script>
<?php require_once 'includes/page_end.php'; ?>
