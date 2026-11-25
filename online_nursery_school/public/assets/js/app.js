document.addEventListener('DOMContentLoaded', function () {
    const openModalButtons = document.querySelectorAll('[data-open-modal]');
    const closeModalButtons = document.querySelectorAll('[data-close-modal]');
    const editPupilButtons = document.querySelectorAll('[data-edit-pupil]');
    const editParentButtons = document.querySelectorAll('[data-edit-parent]');
    const editClassButtons = document.querySelectorAll('[data-edit-class]');
    const editFeeButtons = document.querySelectorAll('[data-edit-fee]');
    const editUserButtons = document.querySelectorAll('[data-edit-user]');
    const liveSearchInput = document.getElementById('liveSearch');
    const feeDueInput = document.getElementById('feeAmountDue');
    const feePaidInput = document.getElementById('feeAmountPaid');
    const feeBalancePreview = document.getElementById('feeBalancePreview');
    const userRoleSelect = document.getElementById('userRole');
    const linkedParentField = document.getElementById('linkedParentField');
    const userParentSelect = document.getElementById('userParent');
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const sidebarNav = document.getElementById('sidebarNav');

    if (mobileMenuToggle && sidebarNav) {
        mobileMenuToggle.addEventListener('click', function () {
            const isOpen = sidebarNav.classList.toggle('open');
            mobileMenuToggle.setAttribute('aria-expanded', String(isOpen));
            mobileMenuToggle.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
        });

        sidebarNav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', function () {
                sidebarNav.classList.remove('open');
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
                mobileMenuToggle.setAttribute('aria-label', 'Open navigation menu');
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                sidebarNav.classList.remove('open');
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
                mobileMenuToggle.setAttribute('aria-label', 'Open navigation menu');
            }
        });
    }

    function updateFeeBalancePreview() {
        if (!feeDueInput || !feePaidInput || !feeBalancePreview) {
            return;
        }

        const due = Number(feeDueInput.value) || 0;
        const paid = Number(feePaidInput.value) || 0;
        feePaidInput.max = due > 0 ? due.toFixed(2) : '0';

        if (paid > due) {
            feeBalancePreview.textContent = 'Amount paid cannot exceed the amount due.';
            feeBalancePreview.classList.add('validation-error');
            feePaidInput.setCustomValidity('Amount paid cannot exceed the amount due.');
            return;
        }

        feePaidInput.setCustomValidity('');
        feeBalancePreview.classList.remove('validation-error');
        feeBalancePreview.textContent = 'Amount still expected: RWF ' + Math.max(0, due - paid).toLocaleString(undefined, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    function updateLinkedParentField() {
        if (!userRoleSelect || !linkedParentField || !userParentSelect) {
            return;
        }

        const needsParent = userRoleSelect.value === 'parent';
        linkedParentField.classList.toggle('hidden', !needsParent);
        userParentSelect.required = needsParent;
        if (!needsParent) {
            userParentSelect.value = '';
        }
    }

    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    openModalButtons.forEach((button) => {
        button.addEventListener('click', function () {
            if (this.dataset.openModal === 'userModal') {
                document.getElementById('userId').value = '0';
                document.getElementById('userFullName').value = '';
                document.getElementById('userUsername').value = '';
                document.getElementById('userEmail').value = '';
                document.getElementById('userRole').value = 'staff';
                document.getElementById('userPassword').value = '';
                document.getElementById('userPassword').required = true;
                updateLinkedParentField();
            }
            if (this.dataset.openModal === 'feeModal') {
                document.getElementById('feeId').value = '0';
                document.getElementById('feePupil').value = '';
                document.getElementById('feeTerm').value = '';
                feeDueInput.value = '';
                feePaidInput.value = '0';
                updateFeeBalancePreview();
            }
            openModal(this.dataset.openModal);
        });
    });

    closeModalButtons.forEach((button) => {
        button.addEventListener('click', function () {
            closeModal(this.dataset.closeModal);
        });
    });

    document.addEventListener('click', function (event) {
        const element = event.target;
        if (element.classList.contains('modal')) {
            element.classList.add('hidden');
        }
    });

    editPupilButtons.forEach((button) => {
        button.addEventListener('click', function () {
            const id = this.dataset.editPupil;
            document.getElementById('pupilId').value = id;
            document.getElementById('pupilFullName').value = this.dataset.name;
            document.getElementById('pupilDob').value = this.dataset.dob;
            document.getElementById('pupilClass').value = this.dataset.class;
            document.getElementById('pupilParent').value = this.dataset.parent;
            openModal('pupilModal');
        });
    });

    editParentButtons.forEach((button) => {
        button.addEventListener('click', function () {
            document.getElementById('parentId').value = this.dataset.editParent;
            document.getElementById('parentName').value = this.dataset.name;
            document.getElementById('parentPhone').value = this.dataset.phone;
            document.getElementById('parentEmail').value = this.dataset.email;
            document.getElementById('parentAddress').value = this.dataset.address;
            openModal('parentModal');
        });
    });

    editClassButtons.forEach((button) => {
        button.addEventListener('click', function () {
            document.getElementById('classId').value = this.dataset.editClass;
            document.getElementById('className').value = this.dataset.name;
            document.getElementById('classTeacher').value = this.dataset.teacher;
            document.getElementById('classDescription').value = this.dataset.description;
            openModal('classModal');
        });
    });

    editFeeButtons.forEach((button) => {
        button.addEventListener('click', function () {
            document.getElementById('feeId').value = this.dataset.editFee;
            document.getElementById('feePupil').value = this.dataset.pupil;
            document.getElementById('feeTerm').value = this.dataset.term;
            document.getElementById('feeAmountDue').value = this.dataset.amountDue;
            document.getElementById('feeAmountPaid').value = this.dataset.amountPaid;
            updateFeeBalancePreview();
            openModal('feeModal');
        });
    });

    editUserButtons.forEach((button) => {
        button.addEventListener('click', function () {
            document.getElementById('userId').value = this.dataset.editUser;
            document.getElementById('userFullName').value = this.dataset.userName;
            document.getElementById('userUsername').value = this.dataset.username;
            document.getElementById('userEmail').value = this.dataset.email;
            document.getElementById('userRole').value = this.dataset.role;
            document.getElementById('userParent').value = this.dataset.parent;
            document.getElementById('userPassword').value = '';
            document.getElementById('userPassword').required = false;
            updateLinkedParentField();
            openModal('userModal');
        });
    });

    if (feeDueInput && feePaidInput) {
        feeDueInput.addEventListener('input', updateFeeBalancePreview);
        feePaidInput.addEventListener('input', updateFeeBalancePreview);
        updateFeeBalancePreview();
    }

    if (userRoleSelect) {
        userRoleSelect.addEventListener('change', updateLinkedParentField);
        updateLinkedParentField();
    }

    if (liveSearchInput) {
        liveSearchInput.addEventListener('input', function () {
            const query = this.value.trim();
            const rows = document.querySelectorAll('#pupilTableBody tr');

            if (query === '') {
                rows.forEach((row) => row.style.display = '');
                return;
            }

            fetch('api/search_pupils.php?q=' + encodeURIComponent(query))
                .then((response) => response.json())
                .then((data) => {
                    const ids = new Set(data.map((pupil) => Number(pupil.id)));
                    rows.forEach((row) => {
                        const firstCell = row.cells[0];
                        if (!firstCell) {
                            return;
                        }
                        const rowId = Number(firstCell.textContent.trim());
                        row.style.display = ids.has(rowId) ? '' : 'none';
                    });
                })
                .catch(() => {
                    rows.forEach((row) => row.style.display = '');
                });
        });
    }
});
