{{-- Global drag-to-scroll untuk .table-responsive biasa (bukan monitoring) --}}
<style>
.table-responsive {
    cursor: default;
}
.table-responsive.drag-scrolling {
    cursor: grabbing !important;
    scroll-behavior: auto !important;
    -webkit-user-select: none;
    user-select: none;
}
.table-responsive.drag-scrolling * {
    pointer-events: none;
}
</style>
<script>
(function () {
    var dragEl = null, startX = 0, startLeft = 0;
    var dblclickRecent = false;

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

        el.addEventListener('dblclick', function () {
            dblclickRecent = true;
            if (dragEl) { dragEl.classList.remove('drag-scrolling'); dragEl = null; }
            setTimeout(function () { dblclickRecent = false; }, 300);
        });

        el.addEventListener('mousedown', function (e) {
            if (dblclickRecent) return;
            if (e.target.closest('input,select,button,a,label,textarea,td,th,code')) return;

            var pending = {
                el:        el,
                startX:    e.pageX - el.getBoundingClientRect().left,
                startLeft: el.scrollLeft,
                pageX:     e.pageX,
                pageY:     e.pageY
            };

            function onMove(e2) {
                var dx = Math.abs(e2.pageX - pending.pageX);
                var dy = Math.abs(e2.pageY - pending.pageY);
                if (dy > 6) { cleanup(); return; }
                if (dx > 12 && dy < 5) {
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
        document.querySelectorAll('.table-responsive').forEach(initDrag);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
</script>
