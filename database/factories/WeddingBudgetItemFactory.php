<?php

namespace Database\Factories;

use App\BudgetCalculationType;
use App\BudgetCategory;
use App\Models\WeddingBudgetItem;
use App\Models\WeddingEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeddingBudgetItem>
 */
class WeddingBudgetItemFactory extends Factory
{
    protected $model = WeddingBudgetItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wedding_event_id' => WeddingEvent::factory(),
            'name' => fake()->words(2, true),
            'category' => fake()->randomElement(BudgetCategory::cases()),
            'calculation_type' => BudgetCalculationType::Fixed,
            'amount' => fake()->randomFloat(2, 50, 5000),
            'is_paid' => false,
            'notes' => null,
            'sort_order' => 0,
        ];
    }

    public function perPerson(): static
    {
        return $this->state(fn (): array => [
            'calculation_type' => BudgetCalculationType::PerPerson,
            'amount' => fake()->randomFloat(2, 20, 80),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (): array => [
            'is_paid' => true,
        ]);
    }
}
