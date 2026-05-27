document.addEventListener('DOMContentLoaded', () => {
  const loader = document.getElementById('loader');
  if (loader) {
    window.addEventListener('load', () => {
      loader.classList.add('hidden');
    });
  }

  const toast = document.querySelector('.toast');
  if (toast) {
    setTimeout(() => toast.classList.add('show'), 150);
    setTimeout(() => toast.classList.remove('show'), 4200);
  }

  document.querySelectorAll('a[data-transition]').forEach((link) => {
    link.addEventListener('click', (event) => {
      if (link.target === '_blank' || event.metaKey || event.ctrlKey) {
        return;
      }
      event.preventDefault();
      document.body.classList.add('fade-out');
      setTimeout(() => {
        window.location.href = link.href;
      }, 200);
    });
  });

  if (window.AOS) {
    AOS.init({
      duration: 800,
      easing: 'ease-out-quart',
      once: true,
      offset: 80,
    });
  }
});
