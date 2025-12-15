<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pre-reservations indexes
        Schema::table('pre_reservations', function (Blueprint $table) {
            if (!$this->indexExists('pre_reservations', 'idx_pre_reservations_hotel_status')) {
            $table->index(['hotel_id', 'status'], 'idx_pre_reservations_hotel_status');
            }
            if (!$this->indexExists('pre_reservations', 'idx_pre_reservations_hotel_created')) {
            $table->index(['hotel_id', 'created_at'], 'idx_pre_reservations_hotel_created');
            }
            if (!$this->indexExists('pre_reservations', 'idx_pre_reservations_group_code')) {
            $table->index('group_code', 'idx_pre_reservations_group_code');
            }
        });

        // Printers indexes
        Schema::table('printers', function (Blueprint $table) {
            if (!$this->indexExists('printers', 'idx_printers_hotel_active')) {
            $table->index(['hotel_id', 'is_active'], 'idx_printers_hotel_active');
            }
            if (!$this->indexExists('printers', 'idx_printers_hotel_module')) {
            $table->index(['hotel_id', 'module'], 'idx_printers_hotel_module');
            }
        });

        // Print logs indexes
        if (Schema::hasTable('print_logs')) {
            Schema::table('print_logs', function (Blueprint $table) {
                if (!$this->indexExists('print_logs', 'idx_print_logs_hotel_created')) {
                $table->index(['hotel_id', 'created_at'], 'idx_print_logs_hotel_created');
                }
                if (!$this->indexExists('print_logs', 'idx_print_logs_printer_created')) {
                $table->index(['printer_id', 'created_at'], 'idx_print_logs_printer_created');
                }
            });
        }

        // Activity logs indexes
        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                if (!$this->indexExists('activity_logs', 'idx_activity_logs_hotel_created')) {
                $table->index(['hotel_id', 'created_at'], 'idx_activity_logs_hotel_created');
                }
                if (!$this->indexExists('activity_logs', 'idx_activity_logs_user_created')) {
                $table->index(['user_id', 'created_at'], 'idx_activity_logs_user_created');
                }
            });
        }

        // Users indexes
        Schema::table('users', function (Blueprint $table) {
            // Check if index doesn't already exist
            if (!$this->indexExists('users', 'idx_users_hotel_id')) {
                $table->index('hotel_id', 'idx_users_hotel_id');
            }
        });

        // Settings indexes  
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (!$this->indexExists('settings', 'idx_settings_hotel_key')) {
                $table->index(['hotel_id', 'key'], 'idx_settings_hotel_key');
                }
            });
        }

        // Identity documents indexes
        if (Schema::hasTable('identity_documents')) {
            Schema::table('identity_documents', function (Blueprint $table) {
                if (!$this->indexExists('identity_documents', 'idx_identity_documents_prereservation')) {
                $table->index('pre_reservation_id', 'idx_identity_documents_prereservation');
                }
            });
        }

        // Signatures indexes
        if (Schema::hasTable('signatures')) {
            Schema::table('signatures', function (Blueprint $table) {
                if (!$this->indexExists('signatures', 'idx_signatures_prereservation')) {
                $table->index('pre_reservation_id', 'idx_signatures_prereservation');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_reservations', function (Blueprint $table) {
            if ($this->indexExists('pre_reservations', 'idx_pre_reservations_hotel_status')) {
            $table->dropIndex('idx_pre_reservations_hotel_status');
            }
            if ($this->indexExists('pre_reservations', 'idx_pre_reservations_hotel_created')) {
            $table->dropIndex('idx_pre_reservations_hotel_created');
            }
            if ($this->indexExists('pre_reservations', 'idx_pre_reservations_group_code')) {
            $table->dropIndex('idx_pre_reservations_group_code');
            }
        });

        Schema::table('printers', function (Blueprint $table) {
            if ($this->indexExists('printers', 'idx_printers_hotel_active')) {
            $table->dropIndex('idx_printers_hotel_active');
            }
            if ($this->indexExists('printers', 'idx_printers_hotel_module')) {
            $table->dropIndex('idx_printers_hotel_module');
            }
        });

        if (Schema::hasTable('print_logs')) {
            Schema::table('print_logs', function (Blueprint $table) {
                if ($this->indexExists('print_logs', 'idx_print_logs_hotel_created')) {
                $table->dropIndex('idx_print_logs_hotel_created');
                }
                if ($this->indexExists('print_logs', 'idx_print_logs_printer_created')) {
                $table->dropIndex('idx_print_logs_printer_created');
                }
            });
        }

        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                if ($this->indexExists('activity_logs', 'idx_activity_logs_hotel_created')) {
                $table->dropIndex('idx_activity_logs_hotel_created');
                }
                if ($this->indexExists('activity_logs', 'idx_activity_logs_user_created')) {
                $table->dropIndex('idx_activity_logs_user_created');
                }
            });
        }

        if ($this->indexExists('users', 'idx_users_hotel_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('idx_users_hotel_id');
            });
        }

        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if ($this->indexExists('settings', 'idx_settings_hotel_key')) {
                $table->dropIndex('idx_settings_hotel_key');
                }
            });
        }

        if (Schema::hasTable('identity_documents')) {
            Schema::table('identity_documents', function (Blueprint $table) {
                if ($this->indexExists('identity_documents', 'idx_identity_documents_prereservation')) {
                $table->dropIndex('idx_identity_documents_prereservation');
                }
            });
        }

        if (Schema::hasTable('signatures')) {
            Schema::table('signatures', function (Blueprint $table) {
                if ($this->indexExists('signatures', 'idx_signatures_prereservation')) {
                $table->dropIndex('idx_signatures_prereservation');
                }
            });
        }
    }

    /**
     * Check if an index exists (SQLite compatible)
     */
    private function indexExists(string $table, string $index): bool
    {
        try {
            $indexes = Schema::getConnection()
                ->select("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name=? AND name=?", [$table, $index]);
            return count($indexes) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
};
