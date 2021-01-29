<?php $this->layout('base', ['activeItem' => 'wireguard', 'pageTitle' => $this->t('WireGuard')]); ?>
<?php $this->start('content'); ?>

<h2>Create</h2>
<form class="frm" method="post">
   <button type="submit">Create</button>
</form>

<?php if (0 !== count($wgPeers)): ?>
<h2>Existing</h2>
<table class="tbl">
    <thead>
        <tr>
            <th><?= $this->t('Public Key'); ?></th>
            <th><?= $this->t('IP Address'); ?></th>
            <th><?= $this->t('Created At'); ?> (<?=$this->e(date('T')); ?>)</th>
            <th><?= $this->t('Actions'); ?></th>
        </tr>
    </thead>
    <tbody>
<?php foreach ($wgPeers as $peerInfo): ?>
    <tr>
        <td><span title="<?= $this->e($peerInfo['PublicKey']); ?>"><?= $this->etr($peerInfo['PublicKey'], 15); ?></span></td>
        <td>
            <ul>
<?php foreach ($peerInfo['AllowedIPs'] as $allowedIp): ?>
                <li><?= $this->e($allowedIp); ?></li>
<?php endforeach; ?>
            </ul>
        </td>
        <td>
            <?= $this->d($peerInfo['CreatedAt']->format(DateTime::ATOM)); ?>
        </td>
        <td>
            <form class="frm" method="post" action="wireguard_remove_peer">
                <input type="hidden" name="PublicKey" value="<?= $this->e($peerInfo['PublicKey']); ?>">
                <button type="submit">Remove</button>
            </form>
        </td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php $this->stop('content'); ?>
