async function validateApplicationForm(form) {
    const namePattern = /^[a-zA-Z\s\.]+$/;

    const nameInputs = form.querySelectorAll('input[type="text"]');
    for (const input of nameInputs) {
        if (input.name && (input.name.includes('name') || input.placeholder.toLowerCase().includes('name'))) {
            if (!namePattern.test(input.value.trim())) {
                alert('Please enter a valid name (only alphabets and spaces allowed).');
                input.focus();
                return false;
            }
            if (input.value.trim().length < 3) {
                alert('Name must be at least 3 characters long.');
                input.focus();
                return false;
            }
        }
    }

    const phoneInput = form.querySelector('input[type="tel"]');
    if (phoneInput && phoneInput.value.length !== 10) {
        alert('Please enter exactly 10 digits for the contact number.');
        phoneInput.focus();
        return false;
    }

    const dobInput = form.querySelector('input[type="date"]');
    if (dobInput && dobInput.value) {
        const selectedDate = new Date(dobInput.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (selectedDate >= today) {
            alert('Date of Birth cannot be today or in the future.');
            dobInput.focus();
            return false;
        }
    }

    const fileInput = form.querySelector('input[type="file"]');
    if (fileInput && fileInput.required && !fileInput.files.length) {
        alert('Please upload your resume.');
        fileInput.focus();
        return false;
    }
    if (fileInput && fileInput.value) {
        const allowedExtensions = /(\.pdf|\.doc|\.docx)$/i;
        if (!allowedExtensions.test(fileInput.value)) {
            alert('Invalid file type! Please upload a PDF or DOC file.');
            fileInput.value = '';
            return false;
        }
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.textContent : '';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
    }

    try {
        const formData = new FormData(form);
        const response = await fetch('submit_application.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            alert(result.message);
            form.reset();
        } else {
            alert(result.message || 'Submission failed. Please try again.');
        }
    } catch (err) {
        alert('Network error. Please check your connection and try again.');
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }

    return false;
}
