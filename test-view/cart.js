document.addEventListener("DOMContentLoaded", () => {
  // --- Elemen cart ---
  const subtotalEl = document.querySelector(".summary-line.subtotal span");
  const discountEl = document.querySelector(".summary-line.discount span");
  const totalEl = document.querySelector(".summary-line.total span");
  const deliveryFeeEl = document.querySelector(".summary-line.delivery span");

  const deliveryFee = 15000; // Rp 15.000
  const discountRate = 0.2;  // 20%

  // Format number ke Rupiah
  const formatRupiah = (amount) => `Rp ${amount.toLocaleString("id-ID")}`;

  // --- Update totals ---
  const updateCart = () => {
    const cartItems = document.querySelectorAll(".cart-item");
    let subtotal = 0;

    cartItems.forEach(item => {
      const price = parseInt(item.dataset.price);
      const qty = parseInt(item.querySelector(".qty-value").textContent);
      subtotal += price * qty;
    });

    const discount = Math.floor(subtotal * discountRate);
    const total = subtotal - discount + deliveryFee;

    subtotalEl.textContent = formatRupiah(subtotal);
    discountEl.textContent = `- ${formatRupiah(discount)}`;
    deliveryFeeEl.textContent = formatRupiah(deliveryFee);
    totalEl.textContent = formatRupiah(total);

    return total;
  };

  // --- Setup quantity buttons ---
  const setupQuantityButtons = (item) => {
    const minusBtn = item.querySelector(".qty-btn.minus");
    const plusBtn = item.querySelector(".qty-btn.plus");
    const qtyValue = item.querySelector(".qty-value");

    minusBtn.addEventListener("click", () => {
      let qty = parseInt(qtyValue.textContent);
      if (qty > 1) qty--;
      qtyValue.textContent = qty;
      updateCart();
    });

    plusBtn.addEventListener("click", () => {
      let qty = parseInt(qtyValue.textContent);
      qty++;
      qtyValue.textContent = qty;
      updateCart();
    });
  };

  // --- Setup remove button ---
  const setupRemoveButton = (item) => {
    const removeBtn = item.querySelector(".remove-btn");
    removeBtn.addEventListener("click", () => {
      item.remove();
      updateCart();
    });
  };

  // --- Initialize cart items ---
  const initCart = () => {
    const cartItems = document.querySelectorAll(".cart-item");
    cartItems.forEach(item => {
      setupQuantityButtons(item);
      setupRemoveButton(item);
    });
    updateCart();
  };

  initCart();

  // --- Checkout Modal ---
  const checkoutBtn = document.querySelector(".checkout-btn");
  const modal = document.getElementById("checkoutModal");
  const closeModal = modal?.querySelector(".close");
  const checkoutTotalEl = document.getElementById("checkoutTotal");
  const qrContainer = document.getElementById("qrcode");

  if (checkoutBtn && modal && checkoutTotalEl && closeModal && qrContainer) {
    checkoutBtn.addEventListener("click", () => {
      const total = updateCart(); // pastikan total terbaru
      checkoutTotalEl.textContent = formatRupiah(total);

      // Tampilkan modal
      modal.style.display = "block";

      // Reset QR code dulu
      qrContainer.innerHTML = "";

      // Generate QR code (gunakan QRCode.js)
      new QRCode(qrContainer, {
        text: `Transfer SNACK.IDN Rp${total}`,
        width: 200,
        height: 200
      });
    });

    closeModal.addEventListener("click", () => modal.style.display = "none");

    window.addEventListener("click", (e) => {
      if (e.target === modal) modal.style.display = "none";
    });
  }
});
