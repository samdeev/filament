<?php

namespace App\Models;

use App\Enums\Region;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use function app;

class Conference extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'region' => Region::class,
        'venue_id' => 'integer',

    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Speaker::class);
    }

    public function talks(): BelongsToMany
    {
        return $this->belongsToMany(Talk::class);
    }

    public static function getForm(): array
    {
        return [
            Section::make('Conference Details')
                ->columns(2)
                ->description('Provide some basic information about the conference.')
                ->collapsible()
                ->schema([
                    TextInput::make('name')
                        ->label('Conference Name')
                        ->required()
                        ->minLength(3)
                        ->columnSpanFull()
                        ->helperText('The name of the conference.')
                        ->maxLength(255),
                    MarkdownEditor::make('description')
                        ->columnSpanFull()
                        ->required(),
                    DateTimePicker::make('start_date')
                        ->native(false)
                        ->required(),
                    DateTimePicker::make('end_date')
                        ->native(false)
                        ->required(),

                    Fieldset::make('Status')
                        ->columns(1)
                        ->schema([
                            Select::make('status')
                                ->options([
                                    'Draft' => 'Draft',
                                    'Published' => 'Published',
                                    'Archived' => 'Archived'
                                ])
                                ->native(false)
                                ->required(),
                            Toggle::make('is_published')
                                ->default(false),
                        ]),
                ]),

            Section::make('Location')
                ->columns(2)
                ->schema([
                    Select::make('region')
                        ->live()
                        ->enum(Region::class)
                        ->options(Region::class),
                    Select::make('venue_id')
                        ->searchable()
                        ->preload()
                        ->createOptionForm(Venue::getForm()) // create a new venue without leaving conference create page
                        ->editOptionForm(Venue::getForm()) // edit venue without leaving conference create page
                        ->relationship(
                            'venue',
                            'name',
                            modifyQueryUsing: function (Builder $query, Forms\Get $get) {
                                return $query->where('region', $get('region'));
                            })
                        ->default(null),

                    Actions::make([
                        Action::make('star')
                            ->label('Fill with factory data')
                            ->icon('heroicon-m-star')
                            ->visible(function (string $operation): bool {
                                if ($operation !== 'create') {
                                    return false;
                                }
                                if (! app()->environment('local')) {
                                    return false;
                                }
                                return true;
                            })
                            ->action(function ($livewire) {
                                $data = Conference::factory()->make()->toArray();
                                $livewire->form->fill($data);
                            })
                    ])
                ]),

//            Section::make('Speakers List')
//                ->schema([
//                    CheckboxList::make('speakers')
//                        ->relationship('speakers', 'name')
//                        ->columnSpanFull()
//                        ->columns(3)
//                        ->searchable()
//                        ->options(Speaker::all()->pluck('name', 'id'))
//                        ->required()
//                ])
        ];
    }
}
