// script.js

document.addEventListener("DOMContentLoaded", () => {
  const decreaseBtn = document.getElementById("decrease");
  const increaseBtn = document.getElementById("increase");
  const quantityEl = document.getElementById("quantity");

  let quantity = 1;

  // Kurangin jumlah
  decreaseBtn.addEventListener("click", () => {
    if (quantity > 1) {
      quantity--;
      quantityEl.textContent = quantity;
    }
  });

  // Tambahin jumlah
  increaseBtn.addEventListener("click", () => {
    quantity++;
    quantityEl.textContent = quantity;
  });
});
