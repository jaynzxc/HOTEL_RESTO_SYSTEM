<!-- DISPLAY SUCCESS MESSAGES -->
<?php if (!empty($_SESSION['success'])): ?>
    <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-4">
        <?php foreach ($_SESSION['success'] as $message): ?>
            <p class="text-sm"><i class="fas fa-circle-check mr-2"></i>
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endforeach; ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- DISPLAY ERROR MESSAGES -->
<?php if (!empty($_SESSION['error'])): ?>
    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4">
        <ul class="list-disc list-inside text-sm">
            <?php foreach ($_SESSION['error'] as $field => $message): ?>
                <li>
                    <?php echo htmlspecialchars($message); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>