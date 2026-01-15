// Main site JavaScript

document.addEventListener('DOMContentLoaded', function() {
  console.log('CANVEX Immigration site loaded');

  // FAQ Accordion functionality
  const faqItems = document.querySelectorAll('.faq-item');
  faqItems.forEach(item => {
    const question = item.querySelector('.faq-question');
    if (question) {
      question.addEventListener('click', () => {
        const isActive = item.classList.contains('active');
        // Close all FAQ items
        faqItems.forEach(faq => faq.classList.remove('active'));
        // Open clicked item if it wasn't active
        if (!isActive) {
          item.classList.add('active');
        }
      });
    }
  });

  // Form submissions
  const consultationForm = document.getElementById('consultationForm');
  if (consultationForm) {
    consultationForm.addEventListener('submit', function(e) {
      e.preventDefault();
      // In a real application, this would send data to a server
      alert('Thank you for your consultation request! We will contact you shortly.');
      consultationForm.reset();
    });
  }

  const assessmentForm = document.getElementById('assessmentForm');
  if (assessmentForm) {
    assessmentForm.addEventListener('submit', function(e) {
      e.preventDefault();
      // In a real application, this would send data to a server
      alert('Thank you for submitting your assessment! Our team will review it and contact you within 2-3 business days.');
      assessmentForm.reset();
    });
  }

  const contactForm = document.getElementById('contactForm');
  if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
      e.preventDefault();
      // In a real application, this would send data to a server
      alert('Thank you for your message! We will get back to you soon.');
      contactForm.reset();
    });
  }

  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');
      if (href !== '#' && href.length > 1) {
        const target = document.querySelector(href);
        if (target) {
          e.preventDefault();
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      }
    });
  });
});
