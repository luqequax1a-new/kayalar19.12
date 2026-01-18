<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('ProductQuestions', ({ productId }) => ({
            productId: productId,
            questions: { data: [], current_page: 1, last_page: 1 },
            fetching: false,
            submitting: false,
            showForm: false,
            totalQuestions: 0,
            isLoggedIn: {{ auth()->check() ? 'true' : 'false' }},
            form: {
                customer_name: {!! json_encode(auth()->check() ? auth()->user()->full_name : "") !!},
                customer_email: {!! json_encode(auth()->check() ? auth()->user()->email : "") !!},
                question: ''
            },
            errors: {
                any() { return Object.keys(this.list).length > 0; },
                has(field) { return !!this.list[field]; },
                get(field) { return this.list[field]?.[0] || ''; },
                record(errors) { this.list = errors; },
                clear(field) { delete this.list[field]; },
                reset() { this.list = {}; },
                list: {}
            },

            init() {
                this.fetchQuestions();
            },

            fetchQuestions(page = 1) {
                this.fetching = true;
                axios.get(`/products/${this.productId}/questions?page=${page}`)
                    .then(response => {
                        this.questions = response.data;
                        this.totalQuestions = response.data.total;
                    })
                    .catch(error => {
                        console.error('Error fetching questions:', error);
                    })
                    .finally(() => {
                        this.fetching = false;
                    });
            },

            submitQuestion(event) {
                this.submitting = true;
                this.errors.reset();

                const formData = new FormData(event.target);
                const data = Object.fromEntries(formData.entries());
                
                // Ensure form data from Alpine is included
                data.customer_name = this.form.customer_name;
                data.customer_email = this.form.customer_email;
                data.question = this.form.question;

                axios.post(`/products/${this.productId}/questions`, data, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                })
                    .then(response => {
                        this.form.question = '';
                        if (typeof notify !== 'undefined') {
                            notify(response.data.message);
                        } else {
                            alert(response.data.message);
                        }
                        
                        if (event.target && typeof event.target.reset === 'function') {
                            event.target.reset();
                        }
                    })
                    .catch(error => {
                        if (error.response && error.response.status === 422) {
                            this.errors.record(error.response.data.errors);
                        } else {
                            const msg = error.response?.data?.message || 'Bir hata oluştu.';
                            if (typeof notify !== 'undefined') {
                                notify(msg);
                            } else {
                                alert(msg);
                            }
                        }
                    })
                    .finally(() => {
                        this.submitting = false;
                    });
            },

            changePage(page) {
                if (page < 1 || page > this.questions.last_page) return;
                this.fetchQuestions(page);
                document.getElementById('questions').scrollIntoView({ behavior: 'smooth' });
            },

            scrollIntoForm() {
                const el = document.getElementById('question-form');
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth' });
                    el.querySelector('textarea')?.focus();
                }
            },

            formatDate(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                return date.toLocaleDateString('tr-TR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            },

            trans(key, replace = {}) {
                let translation = FleetCart.langs[key] || key;
                for (let placeholder in replace) {
                    translation = translation.replace(`:${placeholder}`, replace[placeholder]);
                }
                return translation;
            }
        }));
    });

    document.addEventListener('DOMContentLoaded', () => {
        const tabLink = document.querySelector('.product-details-tab a[href="#questions"]');
        if (tabLink) {
            tabLink.addEventListener('shown.bs.tab', () => {
                window.dispatchEvent(new CustomEvent('shown-questions'));
            });
        }
    });
</script>
