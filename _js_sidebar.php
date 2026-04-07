<script>
document.addEventListener('DOMContentLoaded',function(){
    const t=document.getElementById('sidebarToggle'),s=document.getElementById('sidebar'),o=document.getElementById('sidebarOverlay');
    if(t){t.addEventListener('click',function(){s.classList.toggle('show');o.classList.toggle('show')})}
    if(o){o.addEventListener('click',function(){s.classList.remove('show');o.classList.remove('show')})}
    const cur=window.location.pathname.split('/').pop();
    document.querySelectorAll('.sidebar .nav-link').forEach(l=>{
        l.classList.remove('active');
        if(l.getAttribute('href')===cur)l.classList.add('active');
    });
});
</script>
