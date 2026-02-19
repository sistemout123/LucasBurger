<?php

namespace App\Filament\Pages;

use App\Models\Ingredient;
use App\Models\LocalEstoque;
use App\Models\LoteIngrediente;
use App\Services\InventoryService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class Recebimento extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Recebimento';
    protected static ?string $title = 'Receber Mercadoria';
    protected static ?string $slug = 'recebimento';
    protected static string|\UnitEnum|null $navigationGroup = 'Estoque';
    protected static ?int $navigationSort = -1;

    protected string $view = 'filament.pages.recebimento';

    // ── Form state ──
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'local_id' => LocalEstoque::first()?->id,
            'itens' => [['ingredient_id' => null, 'quantidade' => 1, 'criar_lote' => false]],
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('importar_nfe')
                ->label('Importar NFe (SEFAZ)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\TextInput::make('url')
                        ->label('URL do QR Code (SEFAZ-CE)')
                        ->required()
                        ->url()
                        ->columnSpanFull(),
                    \Filament\Forms\Components\Toggle::make('criar_fornecedor')
                        ->label('Criar Fornecedor Automático?')
                        ->helperText('Opcional. Se o CNPJ não existir, criaremos um registro genérico.')
                        ->default(true),
                ])
                ->action(function (array $data) {
                    $this->importarNfe($data['url'], $data['criar_fornecedor']);
                }),
        ];
    }

    public function importarNfe(string $url, bool $criarFornecedor = true): void
    {
        try {
            $scraper = new \App\Services\NFe\SefazCeScraperService();
            $dto = $scraper->scrape($url);

            $reconciler = new \App\Services\NFe\ItemReconcilerService();
            $reconciledItems = $reconciler->reconcile($dto->items);

            $supplierId = null;
            if ($dto->supplier_cnpj) {
                $cleanCnpj = preg_replace('/[^0-9]/', '', $dto->supplier_cnpj);
                $supplier = \App\Models\Supplier::where('cnpj_cpf', $cleanCnpj)
                    ->orWhere('cnpj_cpf', $dto->supplier_cnpj)->first();

                if ($supplier) {
                    $supplierId = $supplier->id;
                } elseif ($criarFornecedor) {
                    $supplier = \App\Models\Supplier::create([
                        'name' => 'Fornecedor NFe ' . $dto->supplier_cnpj,
                        'cnpj_cpf' => $cleanCnpj,
                        'is_active' => true,
                    ]);
                    $supplierId = $supplier->id;
                }
            }

            $currentItems = $this->data['itens'] ?? [];
            if (count($currentItems) === 1 && empty($currentItems[0]['ingredient_id'])) {
                $currentItems = []; // Remove linha fantasma inicial
            }

            foreach ($reconciledItems as $item) {
                // If it wasn't matched, ingredient_id will be null, users must pick it manually
                $currentItems[] = [
                    'ingredient_id' => $item->matched_ingredient_id,
                    'quantidade' => $item->quantity,
                    'unidade' => $item->uom_symbol,
                    'custo_unitario' => number_format($item->unit_price, 2, ',', '.'),
                    'criar_lote' => false,
                    'numero_lote' => null,
                    'supplier_id' => $supplierId,
                    'data_fabricacao' => null,
                    'data_validade' => null,
                ];
            }

            $this->data['itens'] = $currentItems;

            Notification::make()
                ->title('NFe Importada!')
                ->body(count($reconciledItems) . " itens processados da nota fiscal.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao importar NFe')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Dados da Entrada')
                    ->description('Preencha o cabeçalho do recebimento.')
                    ->icon('heroicon-o-document-text')
                    ->columns(3)
                    ->schema([
                        Select::make('local_id')
                            ->label('Local de Destino')
                            ->options(
                                LocalEstoque::with('almoxarifado')
                                    ->where('esta_ativo', true)
                                    ->get()
                                    ->mapWithKeys(fn($l) => [$l->id => "{$l->almoxarifado->nome} → {$l->nome} ({$l->tipo})"])
                            )
                            ->searchable()
                            ->required()
                            ->helperText('Onde os itens serão armazenados')
                            ->prefixIcon('heroicon-o-map-pin'),

                        TextInput::make('doc_referencia')
                            ->label('Nº Nota Fiscal / Documento')
                            ->placeholder('Ex: NF-0042')
                            ->prefixIcon('heroicon-o-document')
                            ->maxLength(100),

                        Textarea::make('notas')
                            ->label('Observações')
                            ->rows(2)
                            ->placeholder('Ex: Entrega parcial, conferir validade...')
                            ->columnSpan(1),
                    ]),

                Section::make('Itens Recebidos')
                    ->description('Adicione os ingredientes recebidos. Use o botão + para mais linhas.')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Repeater::make('itens')
                            ->label('')
                            ->schema([
                                Grid::make(12)->schema([
                                    Select::make('ingredient_id')
                                        ->label('Ingrediente')
                                        ->options(Ingredient::orderBy('name')->pluck('name', 'id'))
                                        ->searchable()
                                        ->required()
                                        ->columnSpan(4)
                                        ->createOptionForm(\App\Filament\Resources\Ingredients\Schemas\IngredientForm::getComponents(false))
                                        ->createOptionUsing(function (array $data) {
                                            return Ingredient::create($data)->id;
                                        })
                                        ->live()
                                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                            if ($state) {
                                                $ingredient = Ingredient::find($state);
                                                if ($ingredient) {
                                                    $set('unidade', $ingredient->unitOfMeasure?->acronym ?? '');
                                                    $set('custo_unitario', number_format($ingredient->unit_cost, 2, ',', '.'));
                                                }
                                            }
                                        }),

                                    TextInput::make('quantidade')
                                        ->label('Qtd')
                                        ->numeric()
                                        ->required()
                                        ->minValue(0.0001)
                                        ->default(1)
                                        ->columnSpan(2),

                                    TextInput::make('unidade')
                                        ->label('Un.')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->columnSpan(1),

                                    TextInput::make('custo_unitario')
                                        ->label('Custo Un.')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->columnSpan(2)
                                        ->prefix('R$'),

                                    Toggle::make('criar_lote')
                                        ->label('Lote?')
                                        ->inline(false)
                                        ->columnSpan(3)
                                        ->live()
                                        ->default(false),

                                    TextInput::make('numero_lote')
                                        ->label('Nº Lote')
                                        ->visible(fn(Get $get) => $get('criar_lote'))
                                        ->placeholder('Ex: BLEND-2026-001')
                                        ->columnSpan(3),

                                    Select::make('supplier_id')
                                        ->label('Fornecedor')
                                        ->visible(fn(Get $get) => $get('criar_lote'))
                                        ->options(\App\Models\Supplier::where('is_active', true)->pluck('name', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->label('Razão Social / Nome')
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('cnpj_cpf')
                                                ->label('CNPJ / CPF')
                                                ->maxLength(20),
                                            TextInput::make('contact_name')
                                                ->label('Contato')
                                                ->maxLength(255),
                                        ])
                                        ->createOptionUsing(function (array $data) {
                                            return \App\Models\Supplier::create($data)->id;
                                        })
                                        ->placeholder('Selecione ou crie um fornecedor...')
                                        ->columnSpan(3),

                                    DatePicker::make('data_fabricacao')
                                        ->label('Fabricação')
                                        ->visible(fn(Get $get) => $get('criar_lote'))
                                        ->columnSpan(3),

                                    DatePicker::make('data_validade')
                                        ->label('Validade')
                                        ->visible(fn(Get $get) => $get('criar_lote'))
                                        ->columnSpan(3),
                                ]),
                            ])
                            ->addActionLabel('+ Adicionar Item')
                            ->reorderable(false)
                            ->cloneable()
                            ->defaultItems(1)
                            ->columns(1),
                    ]),
            ])
            ->statePath('data');
    }

    public function registrar(): void
    {
        $data = $this->form->getState();
        $service = new InventoryService();
        $userId = auth()->id();
        $count = 0;

        foreach ($data['itens'] as $item) {
            $loteId = null;

            if (!empty($item['criar_lote']) && !empty($item['numero_lote'])) {
                $lote = LoteIngrediente::firstOrCreate(
                    [
                        'ingredient_id' => $item['ingredient_id'],
                        'numero_lote' => $item['numero_lote'],
                    ],
                    [
                        'ingredient_id' => $item['ingredient_id'],
                        'numero_lote' => $item['numero_lote'],
                        'data_fabricacao' => $item['data_fabricacao'] ?? null,
                        'data_validade' => $item['data_validade'] ?? null,
                        'supplier_id' => $item['supplier_id'] ?? null,
                    ]
                );
                $loteId = $lote->id;
            }

            $service->registrarEntrada(
                ingredientId: $item['ingredient_id'],
                localId: $data['local_id'],
                quantidade: $item['quantidade'],
                userId: $userId,
                docReferencia: $data['doc_referencia'] ?? null,
                loteId: $loteId,
                notas: $data['notas'] ?? null,
                supplierId: $item['supplier_id'] ?? null
            );

            $count++;
        }

        Notification::make()
            ->title('Recebimento registrado!')
            ->body("{$count} item(s) entrada(s) no estoque com sucesso.")
            ->success()
            ->send();

        $this->form->fill([
            'local_id' => $data['local_id'],
            'doc_referencia' => null,
            'notas' => null,
            'itens' => [['ingredient_id' => null, 'quantidade' => 1, 'criar_lote' => false]],
        ]);
    }
}
