{{-- Global drag-to-scroll: berlaku untuk .table-responsive dan .monitoring-scroll-wrapper --}}
<style>
.table-responsive,
.monitoring-scroll-wrapper {
    cursor: default;
}
.table-responsive.drag-scrolling,
.monitoring-scroll-wrapper.drag-scrolling {
    cursor: grabbing !important;
    scroll-behavior: auto !important;
    -webkit-user-select: none;
    user-select: none;
}
.table-responsive.drag-scrolling *,
.monitoring-scroll-wrapper.drag-scrolling * {
    pointer-events: none;
}
</style>
<script>
(function () {
    var dragEl = null, startX = 0, startLeft = 0;

    document.addEventListener('mousemove', function (e) {
        if (!dragEl) return;
        var x = e.pageX - dragEl.getBoundingClientRect().left;
        dragEl.scrollLeft = startLeft - (x - startX) * 1.5;
    });

    document.addEventListener('mouseup', function () {
        if (!dragEl) return;
        dragEl.classList.remove('drag-scrolling');
        dragEl = null;
    });

    function initDrag(el) {
        if (el._dragInit) return;
        el._dragInit = true;

        el.addEventListener('mousedown', function (e) {
            if (e.target.closest('input,select,button,a,label,textarea')) return;

            var pending = {
                el: el,
                startX: e.pageX - el.getBoundingClientRect().left,
                startLeft: el.scrollLeft,
                pageX: e.pageX
            };

            function onMove(e2) {
                if (Math.abs(e2.pageX - pending.pageX) > 6) {
                    dragEl     = pending.el;
                    startX     = pending.startX;
                    startLeft  = pending.startLeft;
                    el.classList.add('drag-scrolling');
                    cleanup();
                }
            }
            function cleanup() {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onCancel);
            }
            function onCancel() { cleanup(); }

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup',   onCancel);
        });

        // Touch / swipe
        var tStartX = 0, tStartLeft = 0;
        el.addEventListener('touchstart', function (e) {
            tStartX    = e.touches[0].pageX;
            tStartLeft = el.scrollLeft;
        }, { passive: true });
        el.addEventListener('touchmove', function (e) {
            el.scrollLeft = tStartLeft - (e.touches[0].pageX - tStartX);
        }, { passive: true });
    }

    function initAll() {
        document.querySelectorAll('.table-responsive, .monitoring-scroll-wrapper')
            .forEach(initDrag);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
</script>
