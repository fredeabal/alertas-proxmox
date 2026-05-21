<?php $pager->setSurroundCount(1) ?>

<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center mb-0">
        <!-- Primero -->
        <li class="page-item <?= $pager->hasPrevious() ? '' : 'disabled' ?>">
            <a class="page-link link" href="<?= $pager->hasPrevious() ? $pager->getFirst() : 'javascript:void(0)' ?>" aria-label="First">
                <i class="fas fa-angles-left pagination-icon"></i>
            </a>
        </li>
        
        <!-- Anterior -->
        <li class="page-item <?= $pager->hasPrevious() ? '' : 'disabled' ?>">
            <a class="page-link link" href="<?= $pager->hasPrevious() ? $pager->getPrevious() : 'javascript:void(0)' ?>" aria-label="Previous">
                <i class="fas fa-angle-left pagination-icon"></i>
            </a>
        </li>

        <!-- Números (Limitados por setSurroundCount(1)) -->
        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                <a class="page-link link" href="<?= $link['uri'] ?>">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <!-- Siguiente -->
        <li class="page-item <?= $pager->hasNext() ? '' : 'disabled' ?>">
            <a class="page-link link" href="<?= $pager->hasNext() ? $pager->getNext() : 'javascript:void(0)' ?>" aria-label="Next">
                <i class="fas fa-angle-right pagination-icon"></i>
            </a>
        </li>

        <!-- Último -->
        <li class="page-item <?= $pager->hasNext() ? '' : 'disabled' ?>">
            <a class="page-link link" href="<?= $pager->hasNext() ? $pager->getLast() : 'javascript:void(0)' ?>" aria-label="Last">
                <i class="fas fa-angles-right pagination-icon"></i>
            </a>
        </li>
    </ul>
</nav>
