<?php
include 'includes/header.php';

// Handle Add/Edit/Delete Logic Here (omitted for brevity in this example)
// For now, this will just display the list of galleries.
$galleries_result = $conn->query("SELECT * FROM galleries ORDER BY created_at DESC");

?>
<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700">Manage Galleries</h2>
        <a href="manage_galleries.php?action=add" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Add New Gallery</a>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-lg">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="w-3/5 py-3 px-4 uppercase font-semibold text-sm text-left">Title</th>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Date Created</th>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if ($galleries_result->num_rows > 0): ?>
                    <?php while($gallery = $galleries_result->fetch_assoc()): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4"><?php echo htmlspecialchars($gallery['title']); ?></td>
                        <td class="py-3 px-4"><?php echo date('M d, Y', strtotime($gallery['created_at'])); ?></td>
                        <td class="py-3 px-4 text-center">
                            <a href="manage_galleries.php?action=edit&id=<?php echo $gallery['id']; ?>" class="text-blue-500 hover:underline mr-4">Edit</a>
                            <a href="manage_galleries.php?action=delete&id=<?php echo $gallery['id']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Are you sure?');">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-center py-4">No galleries found. Create one to get started.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include 'includes/footer.php';
?>