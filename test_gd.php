<?php
// Small helper to verify if GD is available and give quick guidance for XAMPP users.
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>GD extension check</title>
    <style>body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;line-height:1.6;padding:20px;} .ok{color:green}.bad{color:red} code{background:#f3f4f6;padding:2px 6px;border-radius:4px}</style>
</head>
<body>
    <h1>PHP GD extension check</h1>
    <p>extension_loaded('gd'): <strong class="<?php echo extension_loaded('gd') ? 'ok' : 'bad'; ?>"><?php echo extension_loaded('gd') ? 'true' : 'false'; ?></strong></p>

    <?php if (!extension_loaded('gd')): ?>
        <div style="background:#fff7f7;border-left:4px solid #f87171;padding:12px;border-radius:6px;">
            <strong>GD is not enabled.</strong>
            <p class="small">To enable GD in XAMPP on Windows:</p>
            <ol>
                <li>Open XAMPP Control Panel → click <code>Config</code> next to <strong>Apache</strong> → choose <code>php.ini</code>.</li>
                <li>Find the line that contains <code>;extension=gd</code> or <code>;extension=gd2</code> and remove the leading semicolon so it becomes <code>extension=gd</code> or <code>extension=gd2</code>.</li>
                <li>Save the file and restart Apache from the XAMPP Control Panel.</li>
            </ol>
            <p>If you still don't see GD after restarting, check <code>extension_dir</code> in the same <code>php.ini</code> and ensure the DLLs exist under <code>C:\\xampp\\php\\ext</code>.</p>
        </div>
    <?php else: ?>
        <div style="background:#f6ffed;border-left:4px solid #34d399;padding:12px;border-radius:6px;">
            <strong>GD is enabled.</strong>
            <p>You can now reload the QR page: <code>/admin/generate_qr/index.php</code></p>
        </div>
    <?php endif; ?>

    <h2>phpinfo()</h2>
    <p>The section below includes <code>phpinfo()</code> output where you can find the GD details.</p>
    <div style="background:#f9fafb;border:1px solid #e5e7eb;padding:12px;border-radius:6px;overflow:auto;max-height:60vh;">
        <?php phpinfo(); ?>
    </div>
</body>
</html>
