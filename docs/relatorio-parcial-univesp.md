# Relatório parcial — Desenvolvimento

## 1. Desenvolvimento

### 1.1 Contextualização e problema de pesquisa

Pequenos estabelecimentos de alimentação precisam receber e organizar pedidos, manter informações de produtos e preços atualizadas e acompanhar o atendimento até a conclusão. Quando essas atividades são conduzidas por canais e registros dispersos, podem ocorrer retrabalho, demora na localização de informações e dificuldade de acompanhar a situação de cada pedido. O projeto Foodflix parte desse contexto e propõe estruturar um sistema de informação para apoiar a operação de uma lanchonete de pequeno porte, reunindo o fluxo de pedidos e as rotinas administrativas em um ambiente web.

O problema de pesquisa é formulado da seguinte maneira: **como um sistema de informação web pode apoiar a organização do fluxo de pedidos e das rotinas administrativas de uma lanchonete de pequeno porte?** A investigação considera a modelagem das necessidades, o desenvolvimento incremental da solução e a verificação de seus principais fluxos. Não se presume, nesta etapa, que os resultados já tenham sido medidos em uma operação real.

### 1.2 Objetivos

#### Objetivo geral

Desenvolver e analisar uma solução de sistema de informação web destinada a apoiar a organização do fluxo de pedidos e das rotinas administrativas de uma lanchonete de pequeno porte.

#### Objetivos específicos

- Identificar e delimitar os requisitos relacionados ao catálogo, ao registro e acompanhamento dos pedidos e à administração das informações da loja.
- Modelar os dados e os processos essenciais, estabelecendo as entidades, regras e etapas necessárias ao fluxo de atendimento.
- Implementar a solução de forma incremental, priorizando um fluxo funcional de ponta a ponta.
- Verificar os requisitos implementados por meio de cenários de uso e testes dos fluxos principais.
- Registrar decisões, limitações e oportunidades de evolução observadas durante o desenvolvimento.

### 1.3 Metodologia

O trabalho caracteriza-se como **pesquisa aplicada**, pois busca desenvolver uma solução para uma necessidade prática; **exploratória e descritiva**, por levantar e organizar requisitos e descrever o processo de construção; e de **abordagem predominantemente qualitativa**, uma vez que a análise se concentra nos processos, regras de negócio e adequação funcional, sem coleta estatística de resultados de usuários nesta etapa. Quanto aos procedimentos, será conduzida pesquisa bibliográfica e documental, complementada por estudo de caso do contexto operacional definido para o projeto. Essa classificação segue a organização metodológica apresentada por Gil (2022).

O desenvolvimento será conduzido em etapas. Primeiro, delimita-se o problema e o escopo, registrando os requisitos funcionais e as restrições. Em seguida, elaboram-se a modelagem de dados, a arquitetura e os fluxos principais. A implementação é dividida em funcionalidades menores, integradas progressivamente para permitir a verificação do fluxo completo. Por fim, os cenários previstos são confrontados com o comportamento implementado, e os resultados e limitações são documentados.

Como evidências do processo, serão utilizados os documentos de planejamento e especificação, os modelos de dados e arquitetura, o histórico de implementação e os resultados de verificações funcionais. A análise consistirá em relacionar essas evidências aos objetivos específicos e verificar se os requisitos delimitados foram contemplados. Caso sejam realizadas entrevistas, observações ou testes com participantes externos em etapa futura, seus procedimentos e cuidados éticos deverão ser descritos antes da coleta; eles não são considerados dados já obtidos neste relatório parcial.

### 1.4 Fundamentação teórica

#### Sistemas de informação e organização dos processos

Um sistema de informação articula pessoas, processos, dados e recursos tecnológicos para registrar e disponibilizar informações que apoiam atividades organizacionais. No contexto estudado, a centralização dos dados de produtos e pedidos busca favorecer a consulta e o acompanhamento das etapas do atendimento. A solução é tratada como apoio à organização do processo de trabalho; sua implementação, por si só, não comprova ganhos de produtividade ou satisfação, que dependeriam de avaliação em uso real.

#### Engenharia de software e desenvolvimento incremental

O desenvolvimento de software requer compreender necessidades, especificar requisitos, projetar, implementar e verificar a solução. A divisão do trabalho em incrementos menores permite acompanhar a evolução e revisar decisões à medida que funcionalidades são integradas. Sommerville (2019) apresenta processos de engenharia de software como atividades organizadas para produzir e manter sistemas que atendam a requisitos. Neste projeto, essa perspectiva fundamenta a documentação dos requisitos, a separação das responsabilidades e a verificação dos fluxos essenciais ao longo da implementação.

#### Comércio eletrônico e fluxo de pedidos

No comércio eletrônico, a transação depende de informações compreensíveis sobre itens, valores e condições de atendimento, além do registro consistente do pedido. No recorte do projeto, o fluxo é entendido como uma sequência de consulta ao catálogo, composição do pedido, confirmação e acompanhamento de seu estado pela operação. A modelagem explícita dessas etapas permite definir regras, dados necessários e responsabilidades administrativas. A avaliação de usabilidade e de resultados comerciais, contudo, exigiria pesquisa específica com usuários e dados de operação.

#### Pesquisa aplicada e estudo de caso

A pesquisa aplicada parte de um problema prático e procura produzir conhecimento ou artefato pertinente à situação investigada. O estudo de caso permite examinar em profundidade um contexto delimitado, utilizando diferentes registros e documentos como evidências. Gil (2022) orienta a definição do problema, dos objetivos e dos procedimentos de pesquisa, aspectos utilizados para estruturar este trabalho. Assim, a análise do Foodflix está limitada ao contexto e ao escopo definidos, sem pretensão de generalização automática para todos os estabelecimentos de alimentação.

### 1.5 Delimitação e estágio do trabalho

O escopo considerado é o fluxo web de consulta de produtos, formação e acompanhamento de pedidos, além das funções administrativas necessárias à operação da loja. O projeto está organizado como uma solução para uma única operação comercial. A pesquisa não abrange, neste relatório, comparação entre plataformas de mercado, mensuração de impacto financeiro ou avaliação quantitativa de experiência de usuários. Essas possibilidades podem ser consideradas em etapas posteriores, desde que haja planejamento de coleta e critérios de avaliação.

## Referências

GIL, Antonio Carlos. *Como elaborar projetos de pesquisa*. 7. ed. Barueri: Atlas, 2022.

SOMMERVILLE, Ian. *Engenharia de software*. 10. ed. São Paulo: Pearson, 2019.

## Documentos do projeto consultados

FOODFLIX. *Plano consolidado*. Documento interno do projeto, s.d.

FOODFLIX. *Relatório da Fundação de Dados*. Documento interno do projeto, s.d.
